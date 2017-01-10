<?php

class TimeService
{
    public function __construct()
    {
        //do nothing
    }

    public function getWorkingHours(Array $timeIntervals)
    {
        $holiday = array('2015-10-12');
        $noofholiday = sizeof($holiday);
        $workhours = 0;

        foreach ($timeIntervals as $timeInterval)
        {
            $initialDate = $timeInterval['start'];
            $finalDate = $timeInterval['end'];

            //create all required date time objects
            $firstdate = new DateTime($initialDate);
            $lastdate = new DateTime($finalDate);

            if ($lastdate > $firstdate)
            {
                $first = $firstdate->format('Y-m-d');
                $first = DateTime::createFromFormat('Y-m-d H:i:s', $first . " 00:00:00");
                $last = $lastdate->format('Y-m-d');
                $last = DateTime::createFromFormat('Y-m-d H:i:s', $last . " 23:59:59");
                $workhours = 0;   //working hours

                for ($i = $first; $i <= $last; $i->modify('+1 day')) {
                    $holiday = false;
                    for ($k = 0; $k < $noofholiday; $k++)   //excluding holidays
                    {
                        if ($i == $holiday[$k]) {
                            $holiday = true;
                            break;
                        }
                    }
                    $day = $i->format('l');
                    if ($day === 'Saturday' || $day === 'Sunday')  //excluding saturday, sunday
                        $holiday = true;

                    if (!$holiday) {
                        $ii = $i->format('Y-m-d');
                        $f = $firstdate->format('Y-m-d');
                        $l = $lastdate->format('Y-m-d');
                        if ($l == $f)
                            $workhours += $this->sameday($firstdate, $lastdate);
                        else if ($ii === $f)
                            $workhours += $this->firstday($firstdate);
                        else if ($l === $ii)
                            $workhours += $this->lastday($lastdate);
                        else
                            $workhours += 8;
                    }
                }
            }
        }

        return round($workhours,2);
    }

    private function sameday(DateTime $firstdate, DateTime $lastdate)
    {
        $fmin = $firstdate->format('i');
        $fhour = $firstdate->format('H');
        $lmin = $lastdate->format('i');
        $lhour = $lastdate->format('H');
        if($fhour >=12 && $fhour <14)
            $fhour = 14;
        if($fhour <8)
            $fhour =8;
        if($fhour >=18)
            $fhour =18;
        if($lhour<8)
            $lhour=8;
        if($lhour>=12 && $lhour<14)
            $lhour = 14;
        if($lhour>=18)
            $lhour = 18;
        if($lmin == 0)
            $min = ((60-$fmin)/60)-1;
        else
            $min = ($lmin-$fmin)/60;
        return $lhour-$fhour + $min;
    }

    private function firstday(DateTime $firstdate)   //calculation of hours of first day
    {
        $stmin = $firstdate->format('i');
        $sthour = $firstdate->format('H');
        if($sthour<8)   //time before morning 8
            $lochour = 8;
        else if($sthour>18)
            $lochour = 0;
        else if($sthour >=12 && $sthour<14)
            $lochour = 4;
        else
        {
            $lochour = 18-$sthour;
            if($sthour>=14)
                $lochour-=2;
            if($stmin == 0)
                $locmin =0;
            else
                $locmin = 1-( (60-$stmin)/60);   //in hours
            $lochour -= $locmin;
        }
        return $lochour;
    }

    private function lastday(DateTime $lastdate)   //calculation of hours of last day
    {
        $stmin = $lastdate->format('i');
        $sthour = $lastdate->format('H');
        if($sthour>=18)   //time after 18
            $lochour = 8;
        else if($sthour<8)   //time before morning 8
            $lochour = 0;
        else if($sthour >=12 && $sthour<14)
            $lochour = 4;
        else
        {
            $lochour = $sthour - 8;
            $locmin = $stmin/60;   //in hours
            if($sthour>14)
                $lochour-=2;
            $lochour += $locmin;
        }
        return $lochour;
    }

}