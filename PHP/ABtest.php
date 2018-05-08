<?php

class ABtest{
  /**
  *从多个版本中 按值的比例返回其中一个版本
  *
  */
  function getRandom($_splits = array(50,50),&$random_chance=NULL){
      $random_chance = is_numeric($random_chance) ? (int) $random_chance : mt_rand(0, array_sum($_splits)-1);
      $chosen_version = false;
      $start_point = 0;
      $i=1;
      $default = NULL;
      foreach ($_splits as $version => $probability) {
          if( $i-->0 ){
              $default = $version;
          } 
          if ($random_chance >= $start_point && $random_chance < ($start_point + $probability)) {
              $chosen_version = $version;
              break;
          }
          $start_point += $probability;
      }
      // Security fallback
      if (false === $chosen_version) {
          $chosen_version = $default;
      }
      return $chosen_version;
  }
  /**
     * 运行一个表达式
     * @param type $param_value 表达式里参数名对应的参数值数组
     * @param type $experission 表达式
     * @param type $with_dollar 表达式的参数名是否含$符号
     * @return boolean
     */
    public static function runExperission( $param_value,$experission,$with_dollar=FALSE ) {
        if( empty($param_value) || !is_array($param_value) || empty($experission) ){
            return FALSE;
        }
        if( !$with_dollar ){
            foreach($param_value as $pname=>$pvalue){
                $experission = str_replace($pname, '$'.$pname, $experission);
            }
        }
        $func = create_function('$'.implode(',$', array_keys($param_value)), 'return '.$experission.';');
        return $func(...array_values($param_value));
    }
    /**
     * 获得P-Value值
     * @param int $orgin_avg 原始版本的均值
     * @param double $orgin_size 原始版本的uv值
     * @param double $orgin_variance 原始版本的方差
     * @param int $contrast_avg 对照版本的均值
     * @param double $contrast_size 对照版本的uv值
     * @param double $contrast_variance 对照版本的方差
     */
    public function getPValues($orgin_avg,$orgin_size,$orgin_variance,$contrast_avg,$contrast_size,$contrast_variance) {
        if( empty($contrast_size) || empty($orgin_size) || $orgin_variance<0 || $contrast_variance<0  ){
            return 0;
        }
        $_z_value = sqrt($contrast_variance/$contrast_size + $orgin_variance/$orgin_size);
        $z_value = empty($_z_value) ? 0 : ($contrast_avg - $orgin_avg)/$_z_value;
        $phi = stats_cdf_normal(abs($z_value),0,1,1);
        $result = $z_value > 0 ? 2*(1-$phi) : 2*$phi;
        return round($result,4);
    }
    /**
     * 计算置信区间，对照版本相较原始版本的
     * @param int $orgin_avg 原始版本的均值
     * @param double $orgin_size 原始版本的uv值
     * @param double $orgin_variance 原始版本的方差
     * @param int $contrast_avg 对照版本的均值
     * @param double $contrast_size 对照版本的uv值
     * @param double $contrast_variance 对照版本的方差
     * @param type $confidence_level 实验置信度 α值
     * @param bool $is_percent 是否返回百分比
     */
    public function getConfidenceInterval($orgin_avg,$orgin_size,$orgin_variance,$contrast_avg,$contrast_size,$contrast_variance,$confidence_level,$is_percent=FALSE) {
        if( empty($contrast_size) || empty($orgin_size) || empty($orgin_avg) || $orgin_variance<0 || $contrast_variance<0 ){
            return [];
        }
        $z_value = sqrt($contrast_variance/$contrast_size + $orgin_variance/$orgin_size);
        $data = stats_cdf_normal($confidence_level/2,0,1,2)*$z_value;
        $min = $is_percent ? round( (($contrast_avg - $orgin_avg)-$data)/$orgin_avg,4 ) : round( ($contrast_avg - $orgin_avg)-$data,4 );
        $max = $is_percent ? round( (($contrast_avg - $orgin_avg)+$data)/$orgin_avg,4 ) : round( ($contrast_avg - $orgin_avg)+$data,4 );
        return array('min'=>$min > $max ? $max : $min ,'max'=>$max > $min ? $max : $min ,'degree'=>round( ($contrast_avg - $orgin_avg)/$orgin_avg,4));
    }
    /**
     * 计算统计功效
     * @param int $orgin_avg 原始版本的均值
     * @param double $orgin_size 原始版本的uv值
     * @param double $orgin_variance 原始版本的方差
     * @param int $contrast_avg 对照版本的均值
     * @param double $contrast_size 对照版本的uv值
     * @param double $contrast_variance 对照版本的方差
     * @param type $confidence_level 实验置信度 α值
     * @return type
     */
    public function getStatisticalPower($orgin_avg,$orgin_size,$orgin_variance,$contrast_avg,$contrast_size,$contrast_variance,$confidence_level) {
        if( empty($contrast_size) || empty($orgin_size) || $orgin_variance<0 || $contrast_variance<0 ){
            return 0;
        }
        $_z_value = sqrt($contrast_variance/$contrast_size + $orgin_variance/$orgin_size);
        $z_value = empty($_z_value)? 0 : abs($contrast_avg - $orgin_avg)/$_z_value;
        $data = stats_cdf_normal(1-$confidence_level/2,0,1,2);
        return round(stats_cdf_normal($z_value - $data,0,1,1),4);
    }
    
    /**
     * 获得转化率的方差（二项分布方差）
     * @param type $convt_num 转化人数
     * @param type $total 总人数
     * @return type
     */
    public function getVarianceOfConversionRate($convt_num,$total) {
        return empty($total)? 0 : round((1-$convt_num/$total)*$convt_num/$total,4);
    }

}
