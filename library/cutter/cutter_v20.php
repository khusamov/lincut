<?php

/*
 * Created on 11.08.2011 by Sergey Fedorenko
 * version 2.0
 */

/*
 * Алгоритм составления длинных деталей
 * 
 * Пробуем составить длинную деталь из последнего обрезка, что у нас остался и самых длинных заготовок
 * 
 *  если обрезка хватает, чтобы дополнить большую деталь, то составляем её, а остаток от обрезка используем для последующих деталей
 *  
 *  если обрезка не хватает, то откладываем этот обрезок насовсем, и длинную деталь начинаем с начала самой длинной заготовки.
 *  Получившийся остаток, как и в предыдущем случае используем для последующих деталей
 * 
 * В этом случае длинная деталь будет состоять из наименьшего количества составных частей.
 * 
 */

class Cutter {

	public $cutter_width = 0;

	public $the_best_tail;

	private $m_the_longest_store_key;
	private $m_units = array ();
	private $m_store = array ();

	public $m_best_units = array ();
	public $m_best_tails = array ();

	public $m_best_result;
	public $m_num_big_store;
	public $m_best_store = array ();
	public $m_best_store_tail = array ();

	// для лучшего результата
	public $a_best_result;
	public $a_num_big_store;
	public $a_best_store = array ();
	public $a_best_store_tail = array ();

	// двумерный массив результата
	public $result;
	
	public $iterations = 0;
	
	public $limitOnTime = null;
	public $limitOnCount = null;
	
	
	public function SetLimitOnTime($limit) {
	    $this->limitOnTime = $limit;
	}
	
	public function SetLimitOnCount($limit) {
	    $this->limitOnCount = $limit;
	}

	public function SetStore($store) {

		// заполнять массив заготовок будем исключая одинаковые заготовки
		foreach ($store as $s_key => $s_value) {

			$find_flag = False;
			foreach ($this->m_store as $m_s_key => $m_s_value) {
				if ($s_value == $m_s_value) {
					$find_flag = True;
					break;
				}
			}

			if (!$find_flag)
				$this->m_store[$s_key] = $s_value;
		}

		// будем искать самую длинную заготовку
		$best_val = 0;
		foreach ($this->m_store as $k => $val) {
			if ($val > $best_val) {
				$this->m_the_longest_store_key = $k;
			}
		}
	}

	public function SetCutterWidth($width) {
		$this->cutter_width = $width;
	}
	public function SetUnits($units) {
		$this->m_units = $units;
	}

	public function Review() {

		//         echo "<br/>\n======= "; foreach($this->m_units as $k => $val) {echo $val." ";} echo "=======<br/>\n"; 

		//$m_units_used = array_fill(0, count($this->m_units), 0);
		$m_units_used = array();
		foreach ($this->m_units as $_key => $_value) $m_units_used[$_key] = 0;

		$best_units_used = $m_units_used;

		$num_units = count($this->m_units);

		$summ_tails = 0;

		$index_result = 0;
		$this->m_num_big_store = 0;

		do {
			$best_tail = 1000000;
			$best_num_additional_store = 0; //дополнительных больших заготовок для больших деталей у лучшего результата

			foreach ($this->m_store as $s_key => $s_value) {

				//             	echo "<br/>\nДля заготовки ".$s_key." ".$s_value." разбор остатков (";
				//                foreach ($this->m_units as $u_key => $u_value) {
				//                    if( $best_units_used[$u_key]==1) continue;
				//                    echo " ".$u_value;
				//                }
				//                echo " )";

				$num_additional_store = 0;
				$len_additional_tail = 0; //для учёта обрезков при разметке длянных деталей

				$tail = $s_value;
				$temp_units_used = $m_units_used;

				foreach ($this->m_units as $u_key => $u_value) {

					if ($m_units_used[$u_key] == 1)
						continue; // если деталь уже использована 

					if ($u_value > $this->m_store[$this->m_the_longest_store_key]) { // если деталь больше самой большой заготовки 

						$num_longist = intval($u_value / $this->m_store[$this->m_the_longest_store_key]); //всего нужно самых больших заготовок (целых) для этой детали
						$this_unit_need = $u_value - $num_longist * $this->m_store[$this->m_the_longest_store_key]; // дополнительно нужно для этой детали 

						if ($this_unit_need < $tail) { // хватит ли текущего обрезка, чтобы сделать большую деталь
							$tail -= $this_unit_need;
							$num_additional_store += $num_longist;

							if ($tail < $this->cutter_width)
								$tail = 0; // эта заготовка вышла вся
							else
								$tail -= $this->cutter_width;

						} else { // обрезка не хватит 
							$len_additional_tail += $tail; // последний обрезок выкинем, начнём длинную деталь с начала новой длинной заготовки
							$tail = $this->m_store[$this->m_the_longest_store_key] - $this_unit_need;
							$num_additional_store += $num_longist +1;
						}

						$temp_units_used[$u_key] = 1;

						continue;
					} // конец разбора больших деталей

					if ($tail >= $u_value) {
						$tail -= $u_value;
						$temp_units_used[$u_key] = 1;
						if ($tail < $this->cutter_width) // остаток заготовки очень маленький, меньше ширины ножа
							$tail = 0; // эта заготовка вышла вся
						else
							$tail -= $this->cutter_width;
					}
				}
				if ($tail + $len_additional_tail < $best_tail && $tail != $s_value) {

					$tail += $len_additional_tail; // учёт обрезков перед длинными деталями	
					$best_storekey = $s_key;
					$best_units_used = $temp_units_used;
					$best_num_additional_store = $num_additional_store;

					
					$this->m_best_store[$index_result] = $this->m_store[$s_key];
					//$add_index = 0;
					unset ($this->m_best_result[$index_result]);
					//                     echo "<br/>\n".$index_result."   ".$tail."<".$best_tail."    Заготовка:".$this->m_store[$s_key]."   Остаток:".$tail."   Больш.заготовок:".$best_num_additional_store." (";

					$best_tail = $tail;

					foreach ($this->m_units as $u_key => $u_value) {
						if ($temp_units_used[$u_key] == $m_units_used[$u_key] || $temp_units_used[$u_key] != 1)
							continue;
						$add_index = $u_key;
						$this->m_best_result[$index_result][$add_index] = $u_value;
						//                         echo " ".$u_value;
						//$add_index++;
	    
					}
					//                     echo " )";
					$this->m_best_store_tail[$index_result] = $tail;
				}
			}
			//нашли лучший вариант (какую из заготовок брать)
			$m_units_used = $best_units_used;
			$summ_tails += $best_tail;
			$last_tail = $best_tail;

			//             echo "<br/>\n----------".array_sum($m_units_used)."<".$num_units; 
			$index_result++;

			$this->m_num_big_store += $best_num_additional_store;

		} while (array_sum($m_units_used) < $num_units);

		return $summ_tails - $last_tail;
	}
	
	// Возвращает true, если цикл поиска надо продолжать
	private function checkMainLoop($start_time, $iterations) {
	    return 
	        time() - $start_time < $this->limitOnTime && $this->limitOnTime !== null ||
	        $iterations <= $this->limitOnCount && $this->limitOnCount !== null;
	}

	public function FindTheBest() {

		arsort($this->m_units, SORT_NUMERIC);
		$u_len = count($this->m_units);
		$this->the_best_tail = 1000000000;

		// Защита от зацикливания
		if ($this->limitOnTime === null && $this->limitOnCount === null) $this->SetLimitOnTime(1);
		
		$this->iterations = 0;
		$start_time = time();
		do {

			unset ($this->m_best_result);
			unset ($this->m_best_store);
			unset ($this->m_best_store_tail);

			$this_tail = $this->Review();

			if ($this_tail < $this->the_best_tail) {
				$this->the_best_tail = $this_tail;

				$this->a_best_result = $this->m_best_result;
				$this->a_best_store = $this->m_best_store;
				$this->a_best_store_tail = $this->m_best_store_tail;
				$this->a_num_big_store = $this->m_num_big_store;
			}

			//shuffle($this->m_units); // TODO это переделать, так как shuffle сбивает ключи, а они нужны
			
			$this->iterations++;

		//} while( time() - $start_time < $this->limitOnTime );
		} while($this->checkMainLoop($start_time, $this->iterations));

		unset ($this->result);
		// после нахождения лучшего результата формируем 2-мерный массив
		foreach ($this->a_best_store as $a_key => $a_value) {
			foreach ($this->m_store as $s_key => $s_value) {
				if ($s_value == $a_value) {

					if (isset ($this->result[$s_key])) // есть такая заготовка
						$this->result[$s_key][1]++;
					else
						$this->result[$s_key] = array (
							$s_value,
							1
						);
				}
			}
		}
		// добавляем дополнительные большие заготовки 
		if ($this->a_num_big_store > 0) {
			if (isset ($this->result[$this->m_the_longest_store_key])) // есть такая заготовка
				$this->result[$this->m_the_longest_store_key][1] += $this->a_num_big_store;
			else
				$this->result[$this->m_the_longest_store_key] = array (
					$this->m_store[$this->m_the_longest_store_key],
					$this->a_num_big_store
				);
		}
	}

	public function CheckData() {

		if (count($this->m_units) == 0)
			return False; // нет деталей 	

		$max_store = 0;

		foreach ($this->m_store as $s_key => $s_value) {
			if ($s_value > $max_store)
				$max_store = $s_value;
		}

		if ($max_store == 0)
			return False; // нет заголовок

		return True;
	}
}

//--------------- how to use -----------------



    /* $zagot = array (
    	"VE41BIA04800E" => 4800,
    	"VE41BIA03800E" => 3800,
    );
    
    $detal = array(
        "a11" => 56,
        "a22" => 151,
        "a33" => 234,
        "a44" => 387,
        "a55" => 1000,
        "a66" => 156,
        "a77" => 1151,
        "a88" => 1234,
        "a99" => 1187,
        "a00" => 2000
    ); */




    /* 
    $zagot = array (
    	"VE41BIA04800E" => 6500,
    );
    
    $detal = array(
        "1" => 6000,
        "2" => 500,
        "3" => 6000,
        "4" => 500,
    );
    
    
    $the_cutter = new Cutter();
    $the_cutter->SetStore($zagot);
    $the_cutter->SetUnits($detal);
    $the_cutter->SetCutterWidth(10);
    
    // проверка на ошибки
    if ($the_cutter->CheckData()) {
    
      $the_cutter->SetLimitOnTime(5); // Поиск по времени (время в сек)  
    	$the_cutter->FindTheBest(); // Поиск
    
    	echo "Схема реза:";
    	
    	
    	foreach ($the_cutter->a_best_store as $i => $val) {
    		echo "<br/>\n" . ($i) . ". Заготовка " . $val . " ( ";
    		foreach ($the_cutter->a_best_result[$i] as $j => $unit) {
    			echo "$j: ".$unit . " ";
    		}
    		echo ")  Остаток: " . $the_cutter->a_best_store_tail[$i];
    	}
    	
    	
    	echo "<br/>\nСуммарный остаток (без последнего): " . $the_cutter->the_best_tail;
    	echo "<br/>\nДополнительно больших заготовок: " . $the_cutter->a_num_big_store;
    
    	echo "<br/>\n<br/>\nРезультирующий массив:<br/>\n";
    	foreach ($the_cutter->result as $key => $mass)
    		echo $key . " => ( " . $mass[0] . ", " . $mass[1] . " )<br/>\n";
    		
    	echo "<br/>\nОбработано вариантов: " . $the_cutter->iterations . "<br/>\n";
    	
    } else {
    
    	echo "<br/>\nОшибка входных данных - Нет деталей или нет заготовок";
    	
    }
     */


