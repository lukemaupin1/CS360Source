<?php
//include auth_session.php file on all user panel pages
include("authSession.php");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Partnership Settings</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" href="HomeStyle.css">
    <link rel="stylesheet" href="style.css">
    
    <style>
        .items {
            margin: 50px auto;
            width: 500px;
            padding: 30px 25px;
            background: white;
        }
		.BarterDB {
			font-size: 48px;
			font-weight: bold;
			text-align: center;
			padding-top: 10px;
		}
    </style>
</head>
<body>
    <?php
        require('database.php');

        $userid = $_SESSION['userid']; // Get current user's ID

        // Fetch partner information for the current user
        $query = "SELECT partnerid FROM users WHERE userid = '$userid'";
        $result = mysqli_query($con, $query);
        $userRow = mysqli_fetch_assoc($result);
        $partnerid = $userRow['partnerid'];

        // Determine partnership status message
        if ($partnerid == 0) {
            $partnershipStatus = "No partner added.";
        } else {
            // Check if the partner has reciprocated
            $partnerQuery = "SELECT partnerid, username FROM users WHERE userid = '$partnerid'";
            $partnerResult = mysqli_query($con, $partnerQuery);
            if ($partnerResult && mysqli_num_rows($partnerResult) == 1) {
                $partnerRow = mysqli_fetch_assoc($partnerResult);
                if ($partnerRow['partnerid'] == $userid) {
                    $partnershipStatus = "Partnership accepted with " . htmlspecialchars($partnerRow['username']) . ".";
                } else {
                    $partnershipStatus = "Partnership pending.";
                }
            } else {
                $partnershipStatus = "No partner found with that ID.";
            }
        }

        if (isset($_POST['partner'])) { // If form submitted
            $partnerUsername = stripslashes($_POST['partner']); // Sanitize input
            $partnerUsername = mysqli_real_escape_string($con, $partnerUsername); 
            
            // Check if the username exists and is not the current user
            $query = "SELECT userid FROM users WHERE username = '$partnerUsername' AND userid != '$userid'";
            $result = mysqli_query($con, $query); // Check database & store result
            
            if (mysqli_num_rows($result) == 1) { // If the partner's username is found
                $partner = mysqli_fetch_assoc($result); // Gets row associated with partner
                $partnerid = $partner['userid']; // Stores partner's id

                // Update partner ID for the current user
                $update = "UPDATE users SET partnerid = '$partnerid' WHERE userid = '$userid'"; // Update partnerid column in users
                
                if (mysqli_query($con, $update)) { // If partner set successfully
                    echo "<div class='form'>
                            <h3>Partner set successfully!</h3><br/>
                            <p class='link'>Click here to <a href='dashboard.php'>return to your dashboard</a></p>
                          </div>";
                } else {
                    echo "<div class='form'>
                            <h3>Failed to set partner. Please try again.</h3><br/>
                            <p class='link'><a href='addPartner.php'>Try again</a></p>
                          </div>";
                }
            } else { // If partner's username isn't found or user tried to set themselves
                echo "<div class='form'>
                        <h3>Username not found or invalid. Please try again.</h3><br/>
                        <p class='link'><a href='addPartner.php'>Try again</a></p>
                      </div>";
            }
        } else { // If form isn't submitted yet, display the form
    ?>      

    <div class="BarterDB"> <p>Set Partner</p> </div>

    <div class="items">
        <form method="post" action="addPartner.php">
            <p>
                <label for="partner">Enter Partner's Username:</label>
                <input type="text" id="partner" name="partner" required><br><br>
            </p>
            <input type="submit" value="Add Partner">
        </form>
        <p><?php echo $partnershipStatus; ?></p>
    </div>

    <?php
    }
    ?>  
</body>
</html>
