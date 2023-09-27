<?php

$contents = file_get_contents('https://raw.githubusercontent.com/zoltancsontos/slovak-name-days-json/master/slovak-nameday-list.json');

if(!$contents) {
    die("error downloading the url contwents");
}

$data = json_decode($contents, true);

$formatted = [];
foreach($data as $monthIndex => $names) {
    $monthNumber = (int)$monthIndex+1;

    $formatted[$monthNumber] = [
        'month' => $monthNumber,
        'names' => [

        ]
    ];

    foreach($names as $dayNumber => $dayNames) {
        $dayNumber = (int)$dayNumber;
        $dayNames = explode(",", $dayNames);
        $dayNames = array_map('trim', $dayNames);
        $dayNames = array_filter($dayNames);
        $dayNames = array_unique($dayNames);

        $formatted[$monthNumber]['names'][$dayNumber] = $dayNames;
    }
}

$lines = [
    'BEGIN:VCALENDAR',
    'PRODID:-//webfirst//Slovensky meninovy kalendar//SK',
    'X-WR-CALNAME:Slovenský meninový kalendár',
    'VERSION:2.0',
    'CALSCALE:GREGORIAN',
    'METHOD:PUBLISH',
];

foreach ($formatted as $monthNumber => $data){
    foreach($data['names'] as $dayNumber => $names) {
        $monthNumberStr = str_pad($monthNumber, 2, 0, STR_PAD_LEFT);
        $dayNumberStr = str_pad($dayNumber, 2, 0, STR_PAD_LEFT);

        $lines[] = 'BEGIN:VEVENT';

        //$summary = sprintf('SUMMARY:Meniny májú %s', implode(", ",$names));
        $summary = sprintf('SUMMARY:%s', implode(", ",$names));
        $summary = mb_str_split($summary, 70);
        $summary = array_map('trim', $summary);
        $summary = implode("\r\n ", $summary);

        $lines[] = $summary;
        $lines[] = sprintf('UID:%s', md5(implode(", ",$names)));
        $lines[] = sprintf('DTSTART:2023%s%s', $monthNumberStr, $dayNumberStr);
        $lines[] = sprintf('DTEND:2023%s%s', $monthNumberStr, $dayNumberStr);
        $lines[] = sprintf('RRULE:FREQ=YEARLY;BYMONTH=%d;BYMONTHDAY=%d', $monthNumber, $dayNumber);
        $lines[] = sprintf('DTSTAMP:%s', date('Ymd\THis'));

        $lines[] = 'END:VEVENT';
    }
}

$lines[] = 'END:VCALENDAR';

header('Content-type: text/calendar; charset=utf-8');
echo implode("\n", $lines);