<?php
   include 'components/connection.php';

   if(isset($_COOKIE['user_id'])){
    $user_id = $_COOKIE['user_id'];

   }else{
    $user_id = '';
   }


?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gurushishya - About Page</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="css/user_style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

   <!-- banner section  -->
    <div class="banner">
      <div class="detail">
        <div class="title">
          <a href="index.php">home </a><span><i class="bx bx-chevron-right"></i>about</span>
        </div>
        <h1>about us</h1>
        <p>Dive in and learn React.js from scratch and way more things ..

        </p>
      
      </div>
  
    </div>




<!-- about section  -->
 <div class="about">
  <div class="box-container">
    <div class="box">
      <img src="image/banner.png" class="img" alt="">
      <div class="thumbnail-1">
        <img src="image/oic1.jpg" alt="" width="400px">
      </div>
      <div class="thumbnail-2">
        <img src="image/about.jpg" alt="" width="600px">
      </div>
      <div class="thumbnail-3">
        <img src="image/about0.jpg" alt="">
      </div>
    </div>
  
    <div class="box">
      <div class="title">
        <!-- <span>know about us</span> -->
        <h1>know about gurushihsya</h1>
        <p>GuruShihsya is designed with simplicity and user-friendliness in mind, allowing learners to navigate effortlessly and focus entirely on their educational goals. It emphasizes inclusivity, ensuring that users from different regions, languages, and cultural backgrounds can access and benefit from its content.  </p>

      </div>
      <div class="detail">
        <i class="bx bx1-facebook"></i>
        <div>
          <h3>simplified courses</h3>
          <p>The tough subjects are simplified by our lectureres easily.</p>
        </div>
      </div>
      <div class="detail">
        <i class="bx bx1-facebook"></i>
        <div>
          <h3>learn form anywhere</h3>
          <p>The tough subjects are simplified by our lectureres easily.</p>
        </div>
      </div>
      <div class="detail">
        <i class="bx bx1-facebook"></i>
        <div>
          <h3>experienced teacher</h3>
          <p>The tough subjects are simplified by our lectureres easily.</p>
        </div>
      </div>
      <a href="" class="btn">know more about us</a>
    </div>
  </div>
 </div>



<!-- work section  -->
 <div class="work">
  <div class="box-container">
    <div class="content">
      <div class="heading">
        <span>how we work</span>
        <h1>build your career and upgrade your life</h1>
        <p>Lets go Ltes go you can do it </p>
        <a href="" class="btn">know more about us</a>
      </div>
    </div>
    <div class="img-box">
       <img src="image/about2.png" alt="">
    </div>
  </div>
 </div>


 <!-- team section  -->
  <div class="team">
    <div class="heading">
      <span>Skill Teachers</span>
      <h1>whose inspiration you love</h1>
    </div>
    <div class="box-container">
      <div class="box">
        <img src="image/bibek1.jpg" alt="">
        <h2>Bibek</h2>
        <p>Web Teacher</p>
        <span>Naagpokhari, Ktm</span>
      </div>
      <div class="box">
        <img src="image/samar.png" alt="">
        <h2>Samar Shrestha</h2>
        <p>Java Teacher</p>
        <span>Cabahil, Ktm</span>
      </div>
 
      <div class="box">
        <img src="image/iman.jpg" alt="">
        <h2>Iman Singh</h2>
        <p>Graphics Teacher</p>
        <span>Kapan, Ktm</span>
      </div>

 
  
    </div>
  </div>








<?php include 'components/userfooter.php'; ?>
<script src="js/user_script.js"></script>
</body>
</html>