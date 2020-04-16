<?php
$mysqli = new mysqli('localhost', 'root', '','phpbooking'); 
if(isset($_GET['date'])){
  $date = $_GET['date'];
  $stmt = $mysqli->prepare("SELECT * FROM bookings WHERE date = ?");
  $stmt->bind_param('s',$date);
  $bookings = array();
  if($stmt->execute()){
    $result = $stmt->get_result();
    if($result->num_rows > 0){
      while($row = $result->fetch_assoc()){
        $bookings[] = $row['timeslot'];
      }
      $stmt->close();
    }
  }
}
/*
DB:phpbooking
TABELL: bookings
*/
if(isset($_POST['submit'])){
  $name = $_POST['name'];
  $email = $_POST['email'];
  $timeslot = $_POST['timeslot'];
  $stmt = $mysqli->prepare("SELECT * FROM bookings WHERE date = ? AND timeslot = ?");
  $stmt->bind_param('ss',$date, $timeslot);
  if($stmt->execute()){
    $result = $stmt->get_result();
    if($result->num_rows > 0){
      $msg = "<div class='alert alert-danger'>Already Booked</div>";
    }else{
      $stmt = $mysqli->prepare("INSERT INTO bookings(name,email,date,timeslot) VALUES(?,?,?,?)");
      $stmt->bind_param('ssss',$name,$email,$date,$timeslot);
      $stmt->execute();
      $msg = "<div class='alert alert-success'>Booking Successfull</div>";
      $stmt->close();
    }
  }
}
$duration = 10;
$cleanup = 0;
$start = "09:00";
$end = "15:00";
function timeslots($duration,$cleanup,$start,$end){
  $start = new DateTime($start);
  $end = new DateTime($end);
  $interval = new DateInterval('PT'.$duration.'M');
  $cleanupInterval = new DateInterval('PT'.$cleanup.'M');
  $slots = array();

  for($intStart = $start; $intStart < $end; $intStart->add($interval)->add($cleanupInterval)){
    $endPeriod = clone $intStart;
    $endPeriod->add($interval);
    if($endPeriod>$end){
      break;
    }

    $slots[] = $intStart->format("H:i")." - ".$endPeriod->format("H:i");

  }

  return $slots;

}
?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>Book</title>
  </head>
  <body>
    <div class="container">
      <h1>Book for Date: <?php echo date('m/d/Y',strtotime($date)); ?></h1><hr>
    </div>
    <div class="row">
      <div class="col-md-12">
        <?php echo isset($msg)?$msg:""; ?>
      </div>
      <?php
      $timeslots = timeslots($duration,$cleanup,$start,$end);
      foreach($timeslots as $ts):
      ?>
      <div class="col-md-2">
        <div class="form-group">
          <?php if(in_array($ts,$bookings)): ?>
            <button class="btn btn-danger"><?php echo $ts; ?> booked</button>
          <?php else: ?>
            <button class="btn btn-success book" data-timeslot="<?php echo $ts; ?>"><?php echo $ts; ?></button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>    
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Booking: <span id="slot"></span></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-12">
                <form action="" method="post">
                <div class="form-group">
                  <label for="timeslot">Timeslot</label>
                  <input type="text" class="form-control" readonly name="timeslot" id="timeslot">
                </div>
                <div class="form-group">
                  <label for="name">Name</label>
                  <input type="text" class="form-control" name="name" id="name">
                </div>
                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" class="form-control" name="email" id="email">
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="submit" class="btn btn-primary">Book</button>
          </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script type="text/javascript">
      $(".book").click(function(){
      var timeslot = $(this).attr("data-timeslot");
      $("#slot").html(timeslot);
      $("#timeslot").val(timeslot);
      $("#myModal").modal("show");
    });
    </script>
  </body>
</html>
