<?php
/**
 * BuyPlayTix_Util_CalendarUtils class
 *
 * @package    BuyPlayTix_Util
 * @author     Tim Thomas <tim@buyplaytix.com>
 * @copyright  2010 BuyPlayTix
 * @license    Commercial
 * @version    Release: @package_version@
 */
namespace BuyPlayTix\Util;
class CalendarUtils {
	public static function parseDow($dowString = "") {
		$dow = Array(false, false, false, false, false, false, false);
		$dateParts = str_split($dowString, 2);
		if(count($dateParts) == 0) {
			return false;
		}
		foreach($dateParts as $datePart) {
			if($datePart == "Su") {
				$dow[0] = true;
			}
			elseif($datePart == "Mo") {
				$dow[1] = true;
			}
			elseif($datePart == "Tu") {
				$dow[2] = true;
			}
			elseif($datePart == "We") {
				$dow[3] = true;
			}
			elseif($datePart == "Th") {
				$dow[4] = true;
			}
			elseif($datePart == "Fr") {
				$dow[5] = true;
			}
			elseif($datePart == "Sa") {
				$dow[6] = true;
			}
			else {
				return false;
			}
		}
		return $dow;
	}
	public static function computeSimpleRun($showDates, $conjunction = "; ") {

		$lastMonth = "";
		$lastDay = "";
		$lastYear = "";
		$lastTime = 0;
		$forceYear = false;
		// we should handle single shows up here
		if(count($showDates) == 0) {
			return "";
		}
		if(count($showDates) == 1) {
			return date("M jS", strtotime($showDates[0])) . " at " . date("g:ia", strtotime($showDates[0]));
		}
		// this is complex, but basically we sort by date, then sort by number of shows at each time
		// this means that we'll list the greatest number of shows and their time first, fewer shows next
		// and all times should descend when they're equal
		usort($showDates, array('BuyPlayTix\Util\CalendarUtils', 'dateSort'));
		$resultDates = array();
		
		$dateFormat = "M jS";
		foreach($showDates as $showDate) {
		  $rawtime = strtotime($showDate);
		  if(date("Y", $rawtime) != date("Y")) {
		   $dateFormat = "M jS Y";
		  }
		}
		foreach($showDates as $showDate) {
			$rawtime = strtotime($showDate);
			$resultDates[] = date($dateFormat, $rawtime) . " at " . date("g:ia", $rawtime);
		}
		return implode(", ", $resultDates);
	}

	public static function computeRun($showDates, $conjunction = "; ") {

		$lastMonth = "";
		$lastDay = "";
		$lastYear = "";
		$lastPrintedYear = false;
		$lastTime = 0;
		// we should handle single shows up here
		if(count($showDates) == 0) {
			return "";
		}
		if(count($showDates) == 1) {
			return date("M jS", strtotime($showDates[0])) . " at " . date("g:ia", strtotime($showDates[0]));
		}
		// this is complex, but basically we sort by date, then sort by number of shows at each time
		// this means that we'll list the greatest number of shows and their time first, fewer shows next
		// and all times should descend when they're equal
		usort($showDates, array('BuyPlayTix\Util\CalendarUtils', 'dateSort'));
		$keyedDates = array();
		$timeCounts = array();
		$maxDate = 0;
		foreach($showDates as $showDate) {
			$rawtime = strtotime($showDate);
			$timeKey = date("g:ia", $rawtime);
			$keyedDates[$timeKey][] = $showDate;
			if(count($keyedDates[$timeKey]) > $maxDate) {
				$maxDate = count($keyedDates[$timeKey]);
			}
		}

		// now we short by greatest number of shows to least number of shows
		$groupedDates = array();
		for($i = $maxDate; $i >= 0; $i--) {
			foreach($keyedDates as $timeKey => $dates) {
				if(count($dates) == $i) {
					$groupedDates[$timeKey] = $dates;
				}
			}
		}
		$lastDate = null;
		$lastDateIndex = 0;
		foreach($groupedDates as $numberIndex => $dates) {
			if($lastDate == null) {
				$lastDate = $groupedDates[$numberIndex][0];
				$lastDateIndex = $numberIndex;
				continue;
			}
			if(BuyplayTix\Util\CalendarUtils::dateSort($lastDate, $groupedDates[$numberIndex][0]) > 0) {
				$dateArray1 = $groupedDates[$lastDateIndex];
				$groupedDates[$lastDateIndex] = $groupedDates[$numberIndex];
				$groupedDates[$numberIndex] = $dateArray1;

				$lastDateIndex = $numberIndex;
				$i = 0;
				continue;
			}
		}

		$i = 0;
		$finalDates = array();
		foreach($groupedDates as $numberIndex => $dates) {
			foreach($dates as $date) {
				$finalDates[$i][] = $date;
			}
			$i++;
		}

		$runWithTime = "";
		for($i = 0; $i < count($finalDates); $i++) {
			$showDates = $finalDates[$i];
			$run = "";


			$skippedOne = false;
			for($j = 0; $j < count($showDates); $j++) {
				$date = $showDates[$j];
				$time = strtotime($date);

				$curMonth = date("n", $time);
				$curDay = date("j", $time);
				$curYear = date("Y", $time);
				
				if(strlen($run) == 0) {
				  $dateFormat = "M jS";
 				  if($curYear != date('Y')) {
				    $dateFormat .= " Y";
				  }
					$run = date($dateFormat, $time);
				}
				elseif($curMonth == $lastMonth && $curDay == $lastDay + 1 && $curYear == $lastYear && count($showDates) - 1 != $j) {
					$lastMonth = $curMonth;
					$lastDay = $curDay;
					$lastYear = $curYear;
					$lastTime = $time;
					$skippedOne = true;
					continue;
				}
				else if(count($showDates) - 1 == $j) {
					// final one, finish this up
					if($curDay != $lastDay + 1 && $skippedOne) {
						$datePattern = "jS";
						if($lastPrintedYear) {
						 $datePattern .= " Y";
						}						
						$run .= "-";
						$run .= date($datePattern, $lastTime);
					}
					$datePattern = "";

					$yearChange = false;
					if($curYear != $lastYear || $curYear != date('Y')) {
						$yearChange = true;
					}

					if($curMonth != $lastMonth || $yearChange) {
						$datePattern .= "M ";
					}
					$addDash = false;
					if($curDay == $lastDay + 1) {
						$addDash = true;
					}
					
					$datePattern .= "jS";
					if($yearChange || ($lastPrintedYear && $addDash)) {
					  $lastPrintedYear = true;  
						$datePattern .= " Y";
					}
					$run .= ($addDash ? "-" : ", ");
					$run .= date($datePattern, $time);
					
					$lastPrintedYear = false;
					if($yearChange) {
					  $lastPrintedYear = true;
					}
				}
				elseif($curDay != $lastDay + 1 || $curYear != $lastYear) {
					// handle when we switch days in the current month
					if($skippedOne) {
						$datePattern = "jS";
						if($lastPrintedYear) {
						  $datePattern .= " Y";
						}
						$run .= "-";
						$run .= date($datePattern, $lastTime);
					}

					$datePattern = "";

					$yearChange = false;
					if($curYear != $lastYear || $curYear != date('Y')) {
						$yearChange = true;
					}

					if($curMonth != $lastMonth || $yearChange) {
						$datePattern .= "M ";
					}
					
					$lastPrintedYear = false;
					$datePattern .= "jS";
					if($yearChange) {
					  $lastPrintedYear = true;
						$datePattern .= " Y";
					}
					$run .= ", ";
					$run .= date($datePattern, $time);
					$lastWrite = true;
				}

				$lastMonth = $curMonth;
				$lastDay = $curDay;
				$lastYear = $curYear;
				$lastTime = $time;
				$skippedOne = false;
			}
			// right here, we should be able to run through the previous key, if all
			// dates and times are the same we don't prepend the run, just the 'and and at'
			$allMatch = false;
			if($i > 0) {
				$prevShowDates = $finalDates[$i - 1];
				if(count($prevShowDates) == count($showDates)) {
					$allMatch = true;
					for($j = 0; $j < count($prevShowDates); $j++) {
						if(date('mdY', strtotime($prevShowDates[$j])) != date('mdY', strtotime($showDates[$j]))) {
							$allMatch = false;
							break;
						}
					}
				}
			}

			if($allMatch) {
				$runWithTime .= " and " . date("g:ia", $lastTime);
			} else {
				if($i > 0) {
					$runWithTime .= $conjunction;
				}
				$runWithTime .= $run . " at " . date("g:ia", $lastTime);
			}
		}

		return $runWithTime;
	}
	public static function dateSort($aStr, $bStr)
	{
		$a = intval(strtotime($aStr));
		$b = intval(strtotime($bStr));
		if ($a == $b) return 0;
		return ($a < $b) ? -1 : 1;
	}
    public static function create_minical($production, $rawDates = array())
    {
        // ok so first we get the list of months
        $months = array();
        foreach ($rawDates as $rawDate) {
            $time = strtotime($rawDate);
            $months[date("Ym", $time)][] = array("date" => date("Y-m-d", $time),
                "tag" => date('Y-m-d-g:ia', $time),
            );
        }
        ksort($months);
        
        $output = "";
        foreach ($months as $key => $values) {
            $calendar = mktime(0, 0, 0, substr($key, 4), 1, substr($key, 0, 4));
            $monthLabel = date("M", $calendar);
            
            $curYear = date("Y");
            if ($curYear != substr($key, 0, 4)) {
                $monthLabel .= ' ' . substr($key, 0, 4);
            }
            
            $days_in_month = date("t", $calendar);
            
            $output .= '<table class="minical">';
            $output .= '<thead><tr><th colspan="7">' . $monthLabel . '</th></tr></thead>';
            $output .= '<tbody><tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>';
            
            $dow = 1;
            for ($i = 1; $i <= $days_in_month; $i ++) {
                if ($dow == 1) {
                    $output .= "<tr>";
                }
                $cur_time = mktime(0, 0, 0, substr($key, 4), $i, substr($key, 0, 4));
                $cur_date = date("Y-m-d", $cur_time);
                $date_tag = date("Y-m-d-g:ia", $cur_time);
                
                $cur_dow = date("N", $cur_time);
                if ($i == 1 && $cur_dow < 7) {
                    // handle leading stuff
                    for ($j = $dow; $j <= $cur_dow; $j ++) {
                        $output .= '<td class="empty">&nbsp;</td>';
                        $dow ++;
                    }
                }
                
                $link = "";
                $class = "";
                foreach ($values as $value) {
                    if ($value["date"] == $cur_date) {
                        $class = ' class="showdate"';
                        $link = '<a href="#" onclick="try {jQuery(\'.hashSelected\').removeClass(\'hashSelected\'); jQuery(\'a[name=\\\'' . $value["tag"] . '\\\']\').parents(\'.ticket\').addClass(\'hashSelected\'); jQuery(window).scrollTop(jQuery(\'.hashSelected:first\').position().top); return false; } catch(e) { window.console.log(e); return false;}">';
                    }
                }
                
                $output .= '<td' . $class . '>' . $link . $i . (empty($link) ? '' : '</a>') . '</td>';
                if ($dow == 7) {
                    $output .= "</tr>";
                    $dow = 1;
                } else {
                    $dow ++;
                }
            }
            $processed_final = false;
            for ($j = $dow; $j <= 7; $j ++) {
                $output .= '<td class="empty">&nbsp;</td>';
                $processed_file = true;
            }
            if ($processed_file) {
                $output .= '</tr>';
            }
            
            $output .= '</tbody></table>';
        }
        return $output;
    }
}
