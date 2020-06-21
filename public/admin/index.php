<?php 
if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER']!="invoiceadmin" && $_SERVER['PHP_AUTH_PW']!="Inv123123!") {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Failed to Login I-Invoice Account Management';
    exit;
}


$con=mysqli_connect("i-invoice.c1azhxaf1zub.ap-east-1.rds.amazonaws.com","admin","william123$$$","invoice");
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
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
            join accounts a on a.id = c.id
            join users u on u.account_id = a.id
            where trial_plan is not null
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