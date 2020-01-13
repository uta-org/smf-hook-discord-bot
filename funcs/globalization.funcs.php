<?php

function getEnglishMonthName($foreignMonthName, $setlocale='es_ES'){

  setlocale(LC_ALL, 'en_US');

  $month_numbers = range(1,12);

  foreach($month_numbers as $month)
    $english_months[] = strftime('%B',mktime(0,0,0,$month,1,2011));

  setlocale(LC_ALL, $setlocale);

  foreach($month_numbers as $month)
    $foreign_months[] = strftime('%B',mktime(0,0,0,$month,1,2011));

  return str_replace($foreign_months, $english_months, $foreignMonthName);

}