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

function month_to_number($month, $locale_set = 'es_ES')
{
    $month  = mb_convert_case($month, MB_CASE_LOWER, 'UTF-8');
    $month  = preg_replace('/я$/', 'й', $month); // fix for 'ru_RU'
    $locale =
        setlocale(LC_TIME, '0');
        setlocale(LC_TIME, $locale_set.'.UTF-8');

    $month_number = FALSE;

    for ($i = 1; $i <= 12; $i++)
    {
        $time_month     = mktime(0, 0, 0, $i, 1, 1970);
        $short_month    = date('M', $time_month);
        $short_month_lc = strftime('%b', $time_month);

        if (stripos($month, $short_month) === 0 OR
            stripos($month, $short_month_lc) === 0)
        {
            $month_number = sprintf("%02d", $i);

            break;
        }
    }

    setlocale(LC_TIME, $locale); // return locale back

    return $month_number;
}

public function spanishStrtotime($date_string) {
  $date_string = str_replace('.', '', $date_string); // to remove dots in short names of months, such as in 'janv.', 'févr.', 'avr.', ...
  return strtotime(
    strtr(
      strtolower($date_string), [
        'enero'=>'jan',
        'febrero'=>'feb',
        'marzo'=>'march',
        'abril'=>'apr',
        'mayo'=>'may',
        'junio'=>'jun',
        'julio'=>'jul',
        'agosto'=>'aug',
        'septiembre'=>'sep',
        'octubre'=>'oct',
        'noviembre'=>'nov',
        'diciembre'=>'dec',
        /*'janv'=>'jan',
        'févr'=>'feb',
        'avr'=>'apr',
        'juil'=>'jul',
        'sept'=>'sep',
        'déc'=>'dec',
        'lundi' => 'monday',
        'mardi' => 'tuesday',
        'mercredi' => 'wednesday',
        'jeudi' => 'thursday',
        'vendredi' => 'friday',
        'samedi' => 'saturday',
        'dimanche' => 'sunday',*/
      ]
    )
  );
}