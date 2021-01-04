<?php 
if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER']!="invoiceadmin" && $_SERVER['PHP_AUTH_PW']!="Inv123123!") {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Failed to Login I-Invoice Account Management';
    exit;
}

function str_random($length) {
    $include_chars = "0123456789abcdefghijklmnopqrstuvwxyz";
    /* Uncomment below to include symbols */
    /* $include_chars .= "[{(!@#$%^/&*_+;?\:)}]"; */
    $charLength = strlen($include_chars);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $include_chars [rand(0, $charLength - 1)];
    }
    return $randomString;
}


$con=mysqli_connect("i-invoice.c1azhxaf1zub.ap-east-1.rds.amazonaws.com","admin","william123$$$","invoice");
//$con=mysqli_connect("localhost","i-invoice","i-invoice","i-invoice");
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}

if(isset($_POST["action"]) && $_POST["action"]=="batch_insert"){
    $users = $_POST["users"];
    foreach($users as $index=>$user){
        if($user["email"]!=""){
            $email = $user["email"];
            $password = $user["password"];
            echo "Email: ".$email;
            $isValid = true;
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo " - Invalid email format <br />";
                $isValid = false;
            }
            echo "<div style='color:red;'>";

            $sql = "select count(*) total_users from users where username = '".$email."' ";
            
            $result1 = mysqli_query($con, $sql);
            $row1 = mysqli_fetch_assoc($result1);
            if($row1["total_users"] > 0){
                echo " - Email is already existed in DB <br />";
                $isValid = false;
            }
            echo "</div>";
            
            if(!$isValid){
                continue;
            }
            
            mysqli_query($con, "START TRANSACTION");
            try{
                
                //echo "insert companies start<br />";
                if(mysqli_query($con, "INSERT INTO companies (trial_started, trial_plan, created_at, updated_at, discount) values (CURDATE(), 'pro', NOW(), NOW(), 0)")){
                    //echo "insert companies success <br />";
                } else {
                   //echo "insert companies failed <br />";
                }
                $company_id = mysqli_insert_id($con);
                //echo "company_id = ".$company_id."<br />";
                
                $ip = $_SERVER['REMOTE_ADDR'];
                //echo "ip = ".$ip."<br />";
                
                $account_key = str_random(32);
                //echo "account_key = ".$account_key."<br />";
                
                //echo "insert accounts start<br />";
                $sql ="INSERT INTO accounts (timezone_id, date_format_id, datetime_format_id, currency_id, created_at, updated_at, ip, account_key, language_id, header_font_id, body_font_id, company_id, financial_year_start, enabled_modules, all_pages_footer, all_pages_header, show_currency_code, logo_width, logo_height, logo_size, 	start_of_week, tax_rate1, tax_rate2) values (90, 9, 9, 26, NOW(), NOW(), '".$ip."', '".$account_key."', 28, 18, 18, ".$company_id.", '2000-04-01', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)";
                //echo $sql."<br />";
                if(mysqli_query($con, $sql)){
                    //echo "insert accounts success<br />";
                }else{
                    //echo "insert accounts failed<br />";
                }

                $account_id = mysqli_insert_id($con);
                //echo "account_id = ".$account_id."<br />";

                
                $password = '$2y$10$86q8mmi2QDgj7Pu4mEudde17DOu/2DPr4TvmRjAgnV5xnsuG9IPvO';
                //echo "password = ".$password."<br />";
                
                $confirmation_code = str_random(32);
                //echo "confirmation_code = ".$confirmation_code."<br />";
               
                $remember_token = str_random(60);
                //echo "remember_token = ".$remember_token."<br />";
                
                //echo "insert users start<br />";
                $sql = "INSERT INTO users (account_id, created_at, updated_at, username, email, password, confirmation_code, registered, confirmed, remember_token, accepted_terms_version, accepted_terms_timestamp, accepted_terms_ip, permissions) values (".$account_id.", NOW(), NOW(),'".$email."','".$email."','".$password."','".$confirmation_code."',1,1,'".$remember_token."', '1.0.1', NOW(), '".$ip."','')";
                //echo $sql."<br />";;
                if(mysqli_query($con, $sql)){
                    //echo "insert users success<br />";
                }else{
                    //echo "insert users failed<br />";
                }
                
                
                mysqli_query($con, "COMMIT");
                
                //echo "insert account_email_setting start<br />";
                $sql = "INSERT INTO account_email_settings (account_id, created_at, updated_at, email_subject_invoice, email_subject_quote ,email_subject_payment,email_template_invoice,email_template_quote,email_template_payment,email_subject_reminder1,email_subject_reminder2,email_subject_reminder3,email_template_reminder1,email_template_reminder2,email_template_reminder3) values (".$account_id.", NOW(), NOW(),'', '','','','','','','','','','','')";
                //echo $sql."<br />";
                if(mysqli_query($con, $sql)){
                    //echo "insert account_email_setting success<br />";
                }else{
                    //echo "insert account_email_setting failed<br />";
                }
                   
                echo " - insert account :".$email. " Success <br />";

            }catch(Exception $e){
                mysqli_query($con, "ROLLBACK");
            }

        }
    }
}

if(isset($_POST["action"]) && $_POST["action"]=="update"){
    
    $company_id = $_POST["company_id"];
    $trial_started = $_POST["trial_started"];
    $plan = (isset($_POST["plan"]) && $_POST["plan"]!="")?$_POST["plan"]:null;
    $plan_term = (isset($_POST["plan"]) && $_POST["plan"]!="")?"year":null;
    $plan_expires = (isset($_POST["plan"]) && $_POST["plan"]!="")?$_POST["plan_expires"]:null;
    $num_users = (isset($_POST["num_users"]))?intval($_POST["num_users"]):0;
    
    echo $company_id."<br />";
    echo $trial_started."<br />";
    echo $plan."<br />";
    echo $plan_term."<br />";
    echo $plan_expires."<br />";
    echo $num_users."<br />";
    
    
    $result = "false";
    
    
    
    if(!$trial_started || !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$trial_started)){
        $result = "invalid_trial_started";
        mysqli_close($con);
        header("Location: index.php?result=".$result);
        die();
    }
        
    if($plan_expires && !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$plan_expires)){
        $result = "invalid_plan_expires";
        mysqli_close($con);
        header("Location: index.php?result=".$result);
        die();
    }
    
    if(!$num_users || $num_users<=0){
        $result = "invalid_num_users";
        mysqli_close($con);
        header("Location: index.php?result=".$result);
        die();
    }
    
    $sql = "update companies set trial_started = ?, plan = ?, plan_term = ?, plan_expires = ?, num_users = ? where id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'ssssii', $trial_started, $plan, $plan_term, $plan_expires, $num_users, $company_id);
    
    echo $sql;
    echo "<br />";
    
    $update_succcess = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    $result = $update_succcess?"update_success":"update_failed";
    echo $result;
    
    mysqli_close($con);
    header("Location: index.php?result=".$result);
    die();
}

$sql = "select 
                    c.id as company_id,
                    a.name as companyname,
                    u.email as email,
                    u.first_name as first_name,
                    u.last_name as last_name,
                    c.plan,
                    c.plan_term,
                    c.plan_expires,
                    c.plan_price,
                    c.num_users,
                    c.trial_started,
                    DATE_ADD(c.trial_started, INTERVAL 14 DAY) as trial_ended
            from companies c
            join accounts a on a.company_id = c.id
            join users u on u.account_id = a.id
            where 1=1 and u.deleted_at is null and u.email is not null
            and u.is_admin = true ";

if(isset($_GET["keywords"]) && $_GET["keywords"]!=""){
    $sql .= " and (";
    $sql .= " LOWER(a.name) like LOWER('%".$_GET["keywords"]."%') ";
    $sql .= " OR LOWER(u.email) like LOWER('%".$_GET["keywords"]."%') ";
    $sql .= " OR LOWER(u.first_name) like LOWER('%".$_GET["keywords"]."%') ";
    $sql .= " OR LOWER(u.last_name) like LOWER('%".$_GET["keywords"]."%') ";
    $sql .= " )";
}
$sql .= " order by a.name, u.first_name, u.last_name";
$result = mysqli_query($con, $sql);


?>
<h1>I-Invoice Account Management</h1>

<h2>Create Trial Acccount</h2>
<p>
<a href="https://app.i-invoice.net/invoice_now?sign_up=true" target="_blank">Click Here</a> to create trial user for company <br />
*Notes: Refresh this page after create the trial account
</p>

<p>Or you may use batch insert to create accounts</p>
<form action="index.php" method="POST">
    <table width="200" border="1">
        <tr>
    		<th>Email</th>
    		<th>Password</th>
    	</tr>
    	<?php for ($i=0;$i<10;$i++) { ?>
    	<tr>
    		<td><input type="text" name="users[<?php echo $i; ?>][email]" value="<?php if(isset($_POST["users"]) && isset($_POST["users"][$i])) { echo $_POST["users"][$i]["email"]; } ?>" /></td>
    		<td>N36gy8r23</td>
    	</tr>
    	<?php } ?>
    </table>
    <input type="hidden" name="action" value="batch_insert" />
    <input type="submit" value="Batch Insert"/>
</form>

<h3>Existed Accounts</h3>
<?php if(isset($_GET['result'])) { echo $_GET['result']; } ?>
<form action="index.php" method="GET">
	<input type="text" name="keywords" value="<?php if(isset($_GET["keywords"])){ echo $_GET["keywords"]; } ?>" />
	<input type="submit" value="Search"/>
</form>
<table width="100%" border="1">
	<tr>
		<th>Company Name</th>
		<th>User First Name</th>
		<th>User Last Name</th>
		<th>User Email</th>
		<th>Trial Start</th>
		<th>Trial End</th>
		<th>Current Plan</th>
		<th>Current Plan Expiry Date</th>
		<th>Maximum Allowed of Users</th>
		<th></th>
	</tr>
	<?php while ($row = mysqli_fetch_assoc($result)) { ?>
	<form action="index.php" method="POST">
    	<tr>
    		<td><?php echo $row['companyname']; ?></td>
    		<td><?php echo $row['first_name']; ?></td>
    		<td><?php echo $row['last_name']; ?></td>
    		<td><?php echo $row['email']; ?></td>
    		<td><input type="text" name="trial_started" value="<?php echo $row['trial_started']; ?>" /></td>
    		<td><?php echo $row['trial_ended']; ?></td>
    		<td>
    			<select name="plan" >
    				<option value="" <?php if(!$row["plan"]) { ?>selected<?php } ?>>-- No Plan --</option>
    				<option value="pro" <?php if($row["plan"]=="pro") { ?>selected<?php } ?>>Pro</option>
    				<option value="enterprise" <?php if($row["plan"]=="enterprise") { ?>selected<?php } ?>>Enterprise</option>
    			</select>
    		</td>
    		<td><input type="text" name="plan_expires" value="<?php echo $row['plan_expires']; ?>" /></td>
    		<td><input type="text" name="num_users" value="<?php echo $row['num_users']; ?>" /></td>
    		<td>
    			<input type="hidden" name="action" value="update" />
    			<input type="hidden" name="company_id" value="<?php echo $row["company_id"]; ?>" />
    			<input type="submit" value="Update"/>
    		</td>
    	</tr>
	</form>
	<?php } ?>
</table>

<h3>Test Login</h3>
<a href="https://app.i-invoice.net" target="_blank">Click Here</a> to go to i-invoice <br />
<?php mysqli_close($con); ?>