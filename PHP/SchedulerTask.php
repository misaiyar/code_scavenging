<?php
class Task {
    protected $taskId;
    protected $coroutine;
    protected $sendValue = null;
    protected $beforeFirstYield = true;
 
    public function __construct($taskId, Generator $coroutine) {
        $this->taskId = $taskId;
        $this->coroutine = $coroutine;
    }
 
    public function getTaskId() {
        return $this->taskId;
        return new SystemCall(function(Task $task,Scheduler $scheduler){
	        $task->setSendValue($task->getTaskId());
	        $scheduler->schedule($task);
	    });
    }
 
    public function setSendValue($sendValue) {
        $this->sendValue = $sendValue;
    }
 
    public function run() {
        if ($this->beforeFirstYield) {
            $this->beforeFirstYield = false;
            return $this->coroutine->current();
        } else {
            $retval = $this->coroutine->send($this->sendValue);
            $this->sendValue = null;
            return $retval;
        }
    }
 
    public function isFinished() {
        return !$this->coroutine->valid();
    }
}

class Scheduler {
    protected $maxTaskId = 0;
    protected $taskMap = []; // taskId => task
    protected $taskQueue;

    protected $waitingForRead = [];
	protected $waitingForWrite = [];
 
    public function __construct() {
        $this->taskQueue = new SplQueue();
    }
 
    public function newTask(Generator $coroutine) {
        $tid = ++$this->maxTaskId;
        $task = new Task($tid, $coroutine);
        $this->taskMap[$tid] = $task;
        $this->schedule($task);
        return $tid;
    }

    public function killTask($tid){
	    if(!isset($this->taskMap[$tid])){
		    return FALSE;
	    }
	    unset($this->taskMap[$tid]);
	    foreach( $this->taskQueue as $i => $task ){
	    	if( $task->getTaskId() === $tid ){
		    	//unset($this->taskQueue[$i]);
		    	$this->taskQueue->offsetUnset($i);
		    	break;
	    	}
	    }
	    return TRUE;
    }
 
    public function schedule(Task $task) {
        $this->taskQueue->enqueue($task);
    }
 
    public function run() {
		//$this->newTask($this->ioPollTask());
        while (!$this->taskQueue->isEmpty()) {
	        //print_r($this->taskMap);
	        //print_r($this->taskQueue);
            $task = $this->taskQueue->dequeue();
            $retval = $task->run();

 			if($retval instanceof SystemCall ){
	 			$retval($task,$this);
	 			continue;
 			}
            if ($task->isFinished()) {
                unset($this->taskMap[$task->getTaskId()]);
            } else {
                $this->schedule($task);
            }
        }
    }
	 
	public function waitForRead($socket, Task $task) {
	    if (isset($this->waitingForRead[(int) $socket])) {
	        $this->waitingForRead[(int) $socket][1][] = $task;
	    } else {
	        $this->waitingForRead[(int) $socket] = [$socket, [$task]];
	    }
	}
	 
	public function waitForWrite($socket, Task $task) {
	    if (isset($this->waitingForWrite[(int) $socket])) {
	        $this->waitingForWrite[(int) $socket][1][] = $task;
	    } else {
	        $this->waitingForWrite[(int) $socket] = [$socket, [$task]];
	    }
	}

	protected function ioPoll($timeout) {
	    $rSocks = [];
	    foreach ($this->waitingForRead as list($socket)) {
	        $rSocks[] = $socket;
	    }
	 
	    $wSocks = [];
	    foreach ($this->waitingForWrite as list($socket)) {
	        $wSocks[] = $socket;
	    }
	 
	    $eSocks = []; // dummy

	    if ( empty($rSocks) && empty($wSocks) && empty($eSocks) || !stream_select($rSocks, $wSocks, $eSocks, $timeout)) {
	        return;
	    }
	 
	    foreach ($rSocks as $socket) {
	        list(, $tasks) = $this->waitingForRead[(int) $socket];
	        unset($this->waitingForRead[(int) $socket]);
	 
	        foreach ($tasks as $task) {
	            $this->schedule($task);
	        }
	    }
	 
	    foreach ($wSocks as $socket) {
	        list(, $tasks) = $this->waitingForWrite[(int) $socket];
	        unset($this->waitingForWrite[(int) $socket]);
	 
	        foreach ($tasks as $task) {
	            $this->schedule($task);
	        }
	    }
	}

	protected function ioPollTask() {
	    while (true) {
	        if ($this->taskQueue->isEmpty()) {
	            $this->ioPoll(null);
	        } else {
	            $this->ioPoll(0);
	        }
	        yield;
	    }
	}
}


class SystemCall {
    protected $callback;
 
    public function __construct(callable $callback) {
        $this->callback = $callback;
    }
 
    public function __invoke(Task $task, Scheduler $scheduler) {
        $callback = $this->callback;
        return $callback($task, $scheduler);
    }
}

function getTaskId() {
    return new SystemCall(function(Task $task, Scheduler $scheduler) {
        $task->setSendValue($task->getTaskId());
        $scheduler->schedule($task);
    });
}

function newTask(Generator $coroutine) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($coroutine) {
            $task->setSendValue($scheduler->newTask($coroutine));
            $scheduler->schedule($task);
        }
    );
}
 
function killTask($tid) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($tid) {
            $task->setSendValue($scheduler->killTask($tid));
            $scheduler->schedule($task);
        }
    );
}


function task1() {
    for ($i = 1; $i <= 10; ++$i) {
        echo "This is task 1 iteration $i.\n";
        yield;
    }
}
 
function task2() {
    for ($i = 1; $i <= 5; ++$i) {
        echo "This is task 2 iteration $i.\n";
        yield;
    }
}

function task($max) {
    $tid = (yield getTaskId()); // <-- here's the syscall!
    for ($i = 1; $i <= $max; ++$i) {
        echo "This is task $tid iteration $i.\n";
        yield;
    }
}

function childTask() {
    $tid = (yield getTaskId());
    while (true) {
        echo "Child task $tid still alive!\n";
        yield;
    }
}
 
function task3() {
    $tid = (yield getTaskId());
    $childTid = (yield newTask(childTask()));
 
    for ($i = 1; $i <= 6; ++$i) {
        echo "Parent task $tid iteration $i.\n";
        yield;
        if ($i == 3) yield killTask($childTid);
    }
}
 
 
$scheduler = new Scheduler;
 
//$scheduler->newTask(task1());
//$scheduler->newTask(task2());
//$scheduler->newTask(task(10));
//$scheduler->newTask(task(5));

//$scheduler->newTask(task3());

//$scheduler->run();

/* ----------------------------------------------------------- */
function waitForRead($socket) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($socket) {
            $scheduler->waitForRead($socket, $task);
        }
    );
}
 
function waitForWrite($socket) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($socket) {
            $scheduler->waitForWrite($socket, $task);
        }
    );
}
function server($port) {
    echo "Starting server at port $port...\n";
 
    $socket = @stream_socket_server("tcp://localhost:$port", $errNo, $errStr);
    if (!$socket) throw new Exception($errStr, $errNo);
 
    stream_set_blocking($socket, 0);
 
    while (true) {
        yield waitForRead($socket);
        $clientSocket = stream_socket_accept($socket, 0);
        yield newTask(handleClient($clientSocket));
    }
}
 
function handleClient($socket) {
    yield waitForRead($socket);
    $data = trim( fread($socket, 8192) );

    $msg = "Received following request:\n\n{$data}";
    $msgLength = strlen($msg) / 8;
echo  '(',$msgLength,')',$msg,PHP_EOL; 
    $response = <<<RES
HTTP/1.1 200 OK\r
Content-Type: text/plain\r
Content-Length: $msgLength\r
Connection: close\r
\r
$msg
RES;
 
    yield waitForWrite($socket);
    fwrite($socket, $response);
 
    fclose($socket);
}
 
$scheduler = new Scheduler;
//$scheduler->newTask(server(8009));
//$scheduler->run();
/* ----------------------------------------------------------- */


function echoTimes($msg, $max) {
    for ($i = 1; $i <= $max; ++$i) {
        echo "$msg iteration $i\n";
        yield $i;
    }
}
 
function task4() {
    $gr = echoTimes('foo', 10); // print foo ten times
    while ( $gr->valid() ){
    	echo $gr->next();
    }
    echo "---\n";
    $gr2 = echoTimes('bar', 5); // print bar five times
    while ( $gr2->valid() ){
    	echo $gr2->next();
    }
    yield; // force it to be a coroutine
}
$scheduler = new Scheduler;
$scheduler->newTask(task4());
$scheduler->run();
/* ----------------------------------------------------------- */
/*
function gen($n){
	while($n--){
		yield $n;
	}
}
$b = microtime(true);
foreach( gen(100000) as $value ){
	$a =  "Get {$value} \n";
}
echo "Exec 1 Time:",microtime(true)-$b,PHP_EOL;

$b = microtime(true);
for( $i=100000 ;$i>0;$i-- ){
	$a =  "Get {$value} \n";
}
echo "Exec 2 Time:",microtime(true)-$b,PHP_EOL;

function printer() {
    while (true) {
        $string = yield;
        echo 'Receive:',$string,PHP_EOL;
    }
}

$printer = printer();
$data = $printer->send('Hello world!');
var_dump($data);



class A {
    private static $sfoo = 1;
    private $ifoo = 2;
    public function __construct( $ifoo=0 ){
	    $this->ifoo = $ifoo ? $ifoo : $this->ifoo ;
    }
}
$a = 'dsfsd';

$cl1 = static function() use ( $printer,$a ) {
	$printer->send($a);
    return self::$sfoo;
};
$cl2 = function() {
    return $this->ifoo;
};

$cl3 = function() {
    return $this->ifoo;
};

$bcl1 = Closure::bind($cl1, null, 'A');
$obj = new A();
$bcl2 = Closure::bind($cl2,$obj , 'A');

//$ref = new ReflectionObject($obj);
//$ms = $ref->getMethods();
//print_r($ms);

$obj_2 = new A(3);
$bcl3 = $cl3->bindto($obj_2,$obj);

//$ref = new ReflectionObject($obj);
//$ms = $ref->getMethods();
//print_r($ms);exit;
var_dump($bcl2);
var_dump($bcl3);
var_dump($obj);
echo $bcl1(), "\n";
echo $bcl2(), "\n";
echo $bcl3(), "\n";*/
