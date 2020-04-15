<?php

function build_calender($month,$year){
  $mysqli = new mysqli('localhost', 'root', '','phpbooking');
  $stmt = $mysqli->prepare("SELECT * FROM bookings WHERE MONTH(date) = ? AND YEAR(date) = ?");
  $stmt->bind_param('ss',$month,$year);
  $bookings = array();
  if($stmt->execute()){
    $result = $stmt->get_result();
    if($result->num_rows > 0){
      while($row = $result->fetch_assoc()){
        $bookings[] = $row['date'];
      }
      $stmt->close();
    }
  }

  //First o all we'll create an array contaning names o all days in a week.
  $daysOfWeek = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');

  //Then we'll get the irst day o the month that is in the argument of this function
  $firstDayOfMonth = mktime(0,0,0,$month,1,$year);

  //Now getting the numver of days this month contains
  $numberDays = date('t',$firstDayOfMonth);

  //Getting some information about the first day of this month
  $dateComponents = getDate($firstDayOfMonth);

  //getting the name of this month
  $monthName = $dateComponents['month'];

  //getting the index value 0-6 of the first day of this month
  $dayOfWeek = $dateComponents['wday'];

  //getting the current date
  $dateToday = date('Y-m-d');

  //now creating the the html table
  $calendar = "<table class='table table-bordered'>";
  $calendar .= "<center><h2>$monthName $year</h2>";

  $calendar .= "<a class='btn btn-sm btn-primary' href='?month=".date('m',mktime(0,0,0,$month-1,1,$year))."&year=".date('Y',mktime(0,0,0,$month-1,1,$year))."'>Previous Month</a>";
  $calendar .= "<a class='btn btn-sm btn-primary' href='?month=".date('m')."&year=".date('Y')."'>Current Month</a>";
  $calendar .= "<a class='btn btn-sm btn-primary' href='?month=".date('m',mktime(0,0,0,$month+1,1,$year))."&year=".date('Y',mktime(0,0,0,$month+1,1,$year))."'>Next Month</a></center></br>";

  $calendar .= "<tr>";

  //creating the calendar headers
  foreach($daysOfWeek as $day){
    $calendar .="<th class='header'>$day</th>";
  }

  $calendar .= "</tr><tr>";

  //the variable $daysOfWeek will make sure that there must be only 7 columns on our mysql_list_tables
  if($dayOfWeek > 0){
    for($k=0;$k<$dayOfWeek;$k++){
      $calendar .="<td></td>";
    }
  }

  //initiating the day counter
  $currentDay = 1;

  //getting the month number
  $month = str_pad($month, 2, "0", STR_PAD_LEFT);

  while($currentDay <= $numberDays){

    //if secenth colum (suturday) reached, start a new row
    if($dayOfWeek == 7){
      $dayOfWeek = 0;
      $calendar .= "</tr><tr>";
    }

    $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);
    $date = "$year-$month-$currentDayRel";

    $dayname = mb_strtolower(date('I',strtotime($date)));
    $eventNum = 0;
    $today = $date==date('Y-m-d')?"today":"";
    if($date<date('Y-m-d')){
      $calendar .= "<td class=''rel='$date'><h4>$currentDay</h4><button class='btn btn-danger btn-sm'>N/A</button>";
    }else if(in_array($date,$bookings)){
      $calendar .= "<td class=''rel='$date'><h4>$currentDay</h4><button class='btn btn-danger btn-sm'>Already Booked</button>";
    }else{
      $calendar .= "<td class='$today' rel='$date'><h4>$currentDay</h4><a href='book.php?date=$date' class='btn btn-success btn-sm'>Book</a>";
    }

    $calendar .= "</td>";

    //incrementing the counters
    $currentDay++;
    $dayOfWeek++;
  }

  //completing the row o the last week in month, if necessary
  if($dayOfWeek != 7){
    $remaniningDays = 7 - $dayOfWeek;
    for($i=0;$i<$remaniningDays;$i++){
      $calendar .= "<td></td>";
    }
  }

  $calendar .= "</tr>";
  $calendar .= "</table>";

  echo $calendar;
}
?>
<html>
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <style>
    table{
      table-layout: fixed;
    }
    td{
      width: 33%;
    }
    .today{
      background: yellow;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
          <?php
          $dateComponents = getdate();
          if(isset($_GET['month']) && isset($_GET['year'])){
            $month = $_GET['month'];
            $year = $_GET['year'];
          }else{
            $month = $dateComponents['mon'];
            $year = $dateComponents['year'];
          }
          echo build_calender($month,$year);
          ?>
      </div>
    </div>
  </div>
  <!-- Optional JavaScript -->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>
