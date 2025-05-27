<?php
session_start();
include('../db.php');

// Store selected amount into session when coming from the plan selection form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["pay"])) {
    $_SESSION['amt'] = (int)$_POST['pay'];
    $_SESSION['plan'] = "3 month plan";
//     if ($_SESSION['amt'] == 6600) {
//     $_SESSION['plan'] = "3 month plan";
// } else {
//     $_SESSION['plan'] = "6 month plan"; // Optional: handle unexpected amounts
// }
}

$plans = $_SESSION['plan'] ?? '3 Month Plan';

// Retrieve amount from session
$amt = $_SESSION['amt'] ?? 3999;
$amt_paisa = $amt * 100;

// Handle the final form submission after Razorpay payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST["fname"], $_POST["mail"], $_POST["mobile"], $_POST["transaction_id"], $_POST["course_name"])) {

    $name = $conn->real_escape_string($_POST["fname"]);
    $email = $conn->real_escape_string($_POST["mail"]);
    $mobile = $conn->real_escape_string($_POST["mobile"]);
    $transaction_id = $conn->real_escape_string($_POST["transaction_id"]);
    $course_name = $conn->real_escape_string($_POST["course_name"]);
    $paid_amt = $amt;
    $paid_plan = $plans;
    $date = date("Y-m-d H:i:s");

    // Check for duplicate payment
    $checkQuery = "SELECT id FROM paid WHERE transaction_id = '$transaction_id'";
    $result = $conn->query($checkQuery);

    if ($result && $result->num_rows > 0) {
        die("Duplicate transaction.");
    }

    // Insert into DB
    $sql = "INSERT INTO paid (name, email, mobile, transaction_id, date,  course_name, paid_amt ,paid_plan) 
            VALUES ('$name', '$email', '$mobile', '$transaction_id', '$date', '$course_name', '$amt', '$plans')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Payment successful! Your details have been saved.'); window.location.href='../index.html';</script>";
    } else {
        echo "<script>alert('Database error: " . $conn->error . "'); window.history.back();</script>";
    }

    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UI/UX Design</title>
    <link rel="icon" href="../images/main-logo.png">
    <link rel="stylesheet" href="abacus-pay.css">
    <script src="https://kit.fontawesome.com/851e180364.js" crossorigin="anonymous"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <header class="header">
        <a href="../index.html"><img src="../images/main-logo.png" alt="nskc-logo"></a>
        <a href="../index.html">
            <button class="login-btn">Back</button>
        </a>
    </header>

    <main class="content">
        <h1>Start Your <span style="color: dodgerblue;">Crash</span> Class</h1>
    </main>

    <form class="container" action="./ui_and_ux-pay.php" method="POST">
        <div class="form-row">
            <input type="text" name="fname" placeholder="First Name*" required>
            <input type="text" name="lname" placeholder="Last Name*" required>
        </div>
        <input type="email" name="mail" placeholder="Mail Id*" required>
        <input type="text" name="mobile" placeholder="Mobile Number*" required>

        <input type="hidden" name="transaction_id" id="transaction_id">  
        <input type="hidden" name="course_name" value="UI/UX Design Crash Course">  

        <button type="button" class="submit-btn" id="rzp-button1">Pay & Submit</button>
    </form>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h4>Explore More</h4>
                <ul>
                    <li><a href="../daycare/index.html">Day Care</a></li>
                    <li><a href="../course/index.html">Skill Development</a></li>
                    <li><a href="../tution/index.html">Tution</a></li>
                    <li><a href="../internship/index.html">Internship</a></li>
                    <li><a href="#">Online Mock Test</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>NSKC'S</h4>
                <ul>
                    <li><a href="../franchaise/index.html">Franchise</a></li>
                    <li><a href="../tutor/index.php">Become a Tutor</a></li>
                    <li><a href="#">TIN</a></li>
                    <li><a href="../tutor-profile/index.html">Our Creators</a></li>
                    <li><a href="#">Store</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById("rzp-button1").addEventListener("click", function (e) {
            e.preventDefault();

            const name = document.querySelector("input[name='fname']").value.trim();
            const email = document.querySelector("input[name='mail']").value.trim();
            const mobile = document.querySelector("input[name='mobile']").value.trim();

            if (!name || !email || !mobile) {
                alert("Please fill all fields.");
                return;
            }

            var options = {
                key: "rzp_test_gI0NOmBqbaL0VX",
                amount: <?= $amt_paisa ?>, // Amount in paisa
                currency: "INR",
                name: "NSKC ACADEMY",
                description: "FOR YOUR UPSKILLING",
                handler: function (response) {
                    if (response.razorpay_payment_id) {
                        document.getElementById("transaction_id").value = response.razorpay_payment_id;
                        alert("Payment successful! Your details are being submitted.");
                        document.querySelector("form.container").submit();
                    }
                },
                prefill: {
                    name: name,
                    email: email,
                    contact: mobile,
                },
                theme: {
                    color: "#3399cc",
                },
            };

            var rzp1 = new Razorpay(options);
            rzp1.open();
        });
    </script>
</body>
</html>
