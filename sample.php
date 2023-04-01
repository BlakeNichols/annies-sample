<?php
	/*

		sample.php - Blake A. Nichols

		Specifications:
			Create a form using PHP, HTML, and CSS that contains a random number of inputs.
			Validate the user’s input.  The input fields should only accept values from 0 to 100.
			With that input calculate and display:
				Lowest value
				Highest value
				Mean value
				Mode value
				Total of all values
				Bonus 1: 5% sales tax on total value
			Store the numbers and the results in a MySQL database.
			Please, comply with accessibility standards.
			Bonus 2: Calculate and display the same values from all database entries over time.
	*/

	/*

		Considerations in this example:
			- Mobile friendly
			- All of the code is in one file for siplicity
			- Normally the css and javascript would be their own file and included with link/script tags
			- Constants would normally be in a config file ($_SERVER['DOCUMENT_ROOT'] . '/_includes/config.env.php)

		Table Structure:

		CREATE TABLE `values` (
			`id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`value` int NOT NULL DEFAULT '0',
			`active` tinyint unsigned NOT NULL DEFAULT '1',
			`created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
		);
	*/

	# Constants - Normally would be in a master config file or part of a model
	const MYSQL_HOST		= '';
	const MYSQL_USER		= '';
	const MYSQL_PASS		= '';
	const MYSQL_SCHEMA		= '';
	const SALES_TAX_RATE 	= 0.05;

	# Make the database connection
	try {
		$pdo = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_SCHEMA, MYSQL_USER, MYSQL_PASS);
	}
	catch(PDOException $e) {
		exit('Error establishing database connection');
	}

	# Make sure we posted
	if(($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['values']) && is_array($_POST['values'])) {
		try {
			
			# Inserting each record
			$add_values = [];
			foreach($_POST['values'] AS $value) {

				# Sanitize the number
				$value = (int)preg_replace('/\D/', '', $value);
				if(($value < 0) || ($value > 100)) {
					// Typically this would show a graceful error to the user but for this sample we will just exit
					exit('All numbers must be between 0 and 100');
				}

				# Add to the array
				$add_values[] = $value;
			}

			# Preparing our statement
			$sql = "INSERT INTO `values` (`value`) VALUES (:value)";
			$statement = $pdo->prepare($sql);

			# Insert all the values
			foreach($add_values AS $value) {
				$statement->execute(['value' => $value]);
			}
		}
		catch(PDOException $e) {
			exit('Error inserting records: ' . $e->getMessage());
		}
	}

?>
<!doctype html>
<html lang="en">
	<head>
		<title>Sample Project</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=5.0, user-scalable=1">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="mobile-web-app-capable" content="yes">
		<style type="text/css">

			:root{
				--page-background-color: #f9f9f9;
				--border-color: #e9e9e9;
				--main-background-color: white;
			}

			body{
				margin:0;
				padding:0;
				text-align:center;
				background:var(--page-background-color);
				font-size:16px;
			}

			main{
				display:inline-block;
				margin:2vh 2vw;
				width:800px;
				max-width:100%;
				text-align:left;
				background:var(--main-background-color);
				border:1px solid var(--border-color);
			}

			h1{
				font-weight:300;
				font-size:22px;
				padding:1.5vh;
				margin:0;
				text-align: center;
				border-bottom:1px solid var(--border-color);
				background-color:RoyalBlue;
				color:white;
			}

			h2{
				font-weight:300;
				text-align: center;
				font-size:18px;
				padding:1.5vh;
				margin:0;
			}

			hr{
				border:0;
				border-bottom:1px dashed var(--border-color);
				margin:2vh 0;
			}

			form{
				padding:2vw 2vh;
			}

			button{
				padding:10px;
				background-color:RoyalBlue;
				color:white;
				border-radius:3px;
				border:0;
				font-size:1rem;
			}

			.input-grid{
				display:grid;
				grid-template-columns: repeat(3,1fr);
				grid-gap:10px;
			}

			.input-field{ 
				padding:clamp(1vh,15px,2vh);
			}

			.input-field input{
				font-size:1rem;
				padding:10px;
				border:1px solid var(--border-color);
				width:100px;
				margin-left:5px;
			}

			.input-field:has(input:invalid), .input-field-error label {
				color:red;
				font-weight:bold;
			}

			.input-submit{
				text-align:center;
				padding:2vh 0;
			}

			.calculations-grid{
				border-top:1px solid var(--border-color);
				display:grid;
				grid-template-columns:1fr 1fr;
				grid-gap:10px;
			}

			.calculations-grid > div{
				padding:25px;
			}

			.calculations-grid > div:last-child{
				border-left:1px solid var(--border-color);
			}

			.calculations-field{
				padding:2vw 2vh;
				text-align:center;
			}

			.calculations-field:empty{
				color:#737373;
			}

			.calculations-field:empty:before{
				content: 'Nothing to show';
				padding:1.5vh 0;
			}

			.calculations-list{
				display:grid;
				grid-template-columns: 1fr minmax(100px,max-content);
				grid-gap:10px;
				text-align:left;
				font-size:18px;
				align-items:baseline;
			}

			.calculations-list > div:nth-child(2n+1) {
				font-size:16px;
				font-weight:bold;
			}

			.error-box{
				padding:10px;
				background-color:red;
				color:white;
				border-radius:5px;
				box-shadow:0px 2px 2px rgba(0,0,0,0.25);
				margin-bottom:1vh;
			}

			@media (max-width:600px){

				body{
					background-color:var(--main-background-color);
				}

				main{
					margin:0;
					border:0;
				}

				.input-grid{
					grid-template-columns: 1fr;
				}

				.calculations-grid{
					border-top:1px solid var(--border-color);
					grid-template-columns:1fr;
				}

				.calculations-grid > div:last-child{
					border-top:1px solid var(--border-color);
				}
			}
		</style>
		<script type="text/javascript">
		
			// A simple app class
			class appClass {

				constructor() {}

				/* Validating the form */
				validateForm(form) {

					// Defaulting to no issues
					var hasIssues = false;
					
					// Check the fields for values between 1-100
					let inputFields = form.querySelectorAll('input[type="text"]');
					for(let i=0; i<inputFields.length; i++) {
						inputFields[i].closest('.input-field')?.classList.remove('input-field-error');
						var value = inputFields[i].value.replace(/\D/g,'');
						value = ((value == '') ? null : parseInt(value));
						if((value === null) || (value < 0) || (value > 100)) {
							inputFields[i].closest('.input-field')?.classList.add('input-field-error');
							hasIssues = true;
						}
					}

					// If we have issues, alert the user
					if(hasIssues) {
						setTimeout(function(){alert('Please fix the input fields in red and re-submit');}, 50);
					}

					// If we have issues we want to return false to stop the form from submitting
					return !hasIssues;
				}

				/* Updating the calculations on the page */
				updateCalculations(form) {

					// Clear the display to start
					let calculationsDisplay = document.getElementById('calculations-field');
					calculationsDisplay.innerHTML = '';

					// Holder for the values
					var values = [];
					
					// Check the fields for values between 1-100
					let inputFields = form.querySelectorAll('input[type="text"]');
					for(let i=0; i<inputFields.length; i++) {
						var value = inputFields[i].value.replace(/\D/g,'');
						value = ((value == '') ? null : parseInt(value));
						if((value === null) || (value < 0) || (value > 100)) {
							if((value !== null)) {
								inputFields[i].closest('.input-field')?.classList.add('input-field-error');
							}
						}
						else {
							inputFields[i].closest('.input-field')?.classList.remove('input-field-error');
							values.push(value);
						}
					}

					// Nothing to display
					if(values.length == 0) {
						// Nothing to display
						return;
					}

					// Sort the values so we can work with them easily
					values.sort();

					// Get the sales tax rate from the form
					let salesTaxRate = parseFloat(form.querySelector('input[name="sales_tax_rate"]').value);

					// Calculate the values to display
					let lowest 				= values[0];
					let highest 			= values[values.length-1];
					let total 				= values.reduce((sum, a) => sum + a, 0);
					let mean 				= values[Math.floor(values.length / 2)];
					let totalWithSalesTax 	= total + (total * salesTaxRate);

					// Groupping values by count
					var valueGroups = {};
					var maxCount = 0;
					for(let i=0; i<values.length; i++) {
						valueGroups[values[i]] = ((valueGroups[values[i]] ?? 0) + 1);
						maxCount = Math.max(maxCount, valueGroups[values[i]]);
					}

					// Groupping the modes (most common)
					var modes = [];
					for(var value in valueGroups) {
						if(valueGroups[value] == maxCount) {
							modes.push(value);
						}
					}

					// Update the display
					calculationsDisplay.innerHTML = '<div class="calculations-list">' +
														'<div>Lowest Number:</div><div>' + lowest + '</div>' +
														'<div>Highest Number:</div><div>' + highest + '</div>' +
														'<div>Total:</div><div>$' + total.toFixed(2) + '</div>' +
														'<div>Mean:</div><div>' + mean + '</div>' +
														'<div>Mode:</div><div>' + ((modes.length > 1) ? 'Multiple (' + modes.join(', ') + ')' : modes[0]) + '</div>' +
														'<div>Total w/ ' + (salesTaxRate * 100).toFixed(2) + '% Sales Tax:</div><div>$' + totalWithSalesTax.toFixed(2) + '</div>' +
													'</div>';
				}
			};

			// Attach a new instance of the class to the window
			window.app = new appClass();
		</script>
	</head>
	<body>
		<main>
			<?php

				# Random number of fields (made multiple of 3 for display purposes only)
				$number_of_fields = (mt_rand(2,4) * 3);

				# Header
				echo '<h1>Please enter a number between 0 and 100 in the following ', $number_of_fields, ' fields</h1>';

				# Display the form
				echo '	<form method="post" action="', htmlentities($_SERVER['PHP_SELF']), '" onsubmit="return app.validateForm(this);">
							<input type="hidden" name="sales_tax_rate" value="', SALES_TAX_RATE, '" />
							<div class="input-grid">';
								for($i=0; $i<$number_of_fields; $i++) {
									echo '	<div class="input-field">
												<label id="field-', $i,'-label" for="field-', $i, '">Value ', ($i + 1), ':</label>
												<input id="field-', $i, '" aria-labelledby="field-', $i,'-label" type="text" name="values[]" maxlength="3" placeholder="#" pattern="[0-9]{0,3}" onchange="app.updateCalculations(this.form);" onkeyup="app.updateCalculations(this.form);" />
											</div>';
								}
				echo '		</div>
							<div class="input-submit">
								<button type="submit">Submit</button>
							</div>
						</form>';
			?>
			<div class="calculations-grid">
				<div>
					<h2>Form Values</h2>
					<div id="calculations-field" class="calculations-field"></div>
				</div>
				<div>
					<h2>Stored Values</h2>
					<?php

						# Holder for the summed data
						$data 		= null;
						$modes		= null;
						$max_count	= null;

						try {

							# All of the stored values except mode
							$query = "	SELECT
											MIN(`value`) AS `lowest`,
											MAX(`value`) AS `highest`,
											SUM(`value`) AS `total`,
											ROUND(AVG(`value`), 0) AS `mean`
										FROM
											`values`
										WHERE
											`active` = 1";
							$result = $pdo->query($query);

							# Data from the pull
							$data = $result->fetch(PDO::FETCH_OBJ);

							# Load numbers by count
							$query = "	SELECT
											COUNT(1) AS `count`,
											`value`
										FROM
											`values`
										WHERE
											`active` = 1
										GROUP BY
											`value`
										ORDER BY
											`count` DESC";
							$result = $pdo->query($query);
							
							# Data from the pull
							while($mode_data = $result->fetch(PDO::FETCH_OBJ)) {
								if(($max_count === null) || ($mode_data->count == $data->highest)) {
									$modes[] = $mode_data->value;
									$max_count = $mode_data->count;
								}
								else {
									break;
								}
							}
						}

						catch(Exception $e) {
							$data 		= null;
							$mode_data 	= null;
							echo '<div class="error-box">Error loaidng stored values</div>';
						}

						# Display the values
						echo '	<div class="calculations-field">
									<div class="calculations-list">
										<div>Lowest Number:</div>
										<div>', ($data->lowest ?? '-'), '</div>
										<div>Highest Number:</div>
										<div>', ($data->highest ?? '-'), '</div>
										<div>Total:</div>
										<div>', (($data->total !== null) ? '$' . number_format($data->total, 2) : '-'), '</div>
										<div>Mean:</div>
										<div>', ($data->mean ?? '-'), '</div>
										<div>Mode:</div>
										<div>', (
											($modes !== null) ?
											((count($modes) > 1) ? 'Multiple (' . implode(', ', $modes) . ')' : $modes[0]) :
											''
										), '</div>
										<div>Total w/ ', number_format(SALES_TAX_RATE * 100, 2), '% Sales Tax:</div>
										<div>', (($data->total !== null) ? '$' . number_format($data->total + ($data->total * SALES_TAX_RATE), 2) : '-'), '</div>
									</div>
								</div>';
					?>
				</div>
			</div>
		</main>
	</body>
</html>