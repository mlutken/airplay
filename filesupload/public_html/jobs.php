<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<style>
			body {
				background-color: #f0f0f0;
				font-size:12px;
				font-family:Verdana;
			}
			.page_content {
				float:left;
				width:1200px;
			}
			#create_form_container {
				width: 1000px;
				float:left;
			}
			#job_list, #job_status_list {
				color: #767676;
				width: 1200px;
				background: #a5e283;
				border: #337f09 1px solid;
				border-top: #337f09 0px solid;
				padding:10px;
				float:left;
			}
			#form_job_container {
				color: #767676;
				width: 1200px;
				background: #a5e283;
				border: #337f09 1px solid;
				padding:10px;
				float:left;
			}
			.header {
				font-weight:bold;
			}
			.success {
				color: #767676;
				text-align: center;
				width: 1200px;
				background: #a5e283;
				border: #337f09 1px solid;
				padding:10px;
			}
			.error {
				width: 1200px;
				background: #ea7e7e;
				border: #a71010 1px solid;
				text-align: center;
				padding:10px;
				font-weight:bold;
				font-size:16px;
			}
			.queuing {
				color: blue;
			}
			.running {
				color:green;
			}
			.notrunning {
				color:darkblue;
			}
			.disabled  {
				color:red;
			}
			.forced_restart {
				color:red;
				font-weight:bold;
			}
			#job_list td {
				border-bottom:#337f09 1px solid;
				line-height:14px;
				height:14px;
			}
			#job_status_list td {
				width:170px;
				line-height:14px;
				height:14px;
			}
			.content {
				width:500px;
			}
			.label {
				width:150px;
				padding-left:30px;
			}
			.header {
				font-weight:bold;
				font-size:14px;
				padding:10px;
			}
		</style>
		<script type="text/javascript" src="jquery-1.7.2.min.js"></script>
		<script type="text/javascript">
			var sAjaxPath = "jobs_ajax.php";
			$(document).ready(function(){
				$("#create_link").hide();
				$('#create').click(function() {
					$("#job_status_list").text('');
					$("#job_status_list").hide(200);
					$('#status_message').hide(0);
					$.ajax({
						type : 'POST',
						url : sAjaxPath,
						dataType : 'json',
						data: {
							get_type : "admin_create_or_edit_job",
							job_id : $('#job_id').val(),
							job_status_id: $("#job_status_id").val(),
							job_name : $('#job_name').val(),
							job_run_interval_minutes : $('#job_run_interval_minutes').val(),
							estimated_runtime : $('#estimated_runtime').val(),
							script_path : $('#script_path').val(),
							parameters : $('#parameters').val(),
							job_priority : $('#job_priority').val(),
							job_force_restart: $('#job_force_restart').prop('checked')
							
						},
						success : function(data){
							$('#status_message').removeClass().addClass((data.error === true) ? 'error' : 'success')
							$('#status_message').text(data.msg);
							if (data.error === true) {
								$('#status_message').removeClass().addClass('error')
								$('#status_message').show(200);
								$('#form_job_container').show(200);
							} else {
								getJobList();
							}
						},
						error : function(XMLHttpRequest, textStatus, errorThrown) {
							$('#status_message').removeClass().addClass('error')
								.text('There was an error.').show(200);
							$('#form_job_container').show(200);
						}
					});

					return false;
				});
			});
			
			function getJobList() {
				var sHTML = "";
				$('#job_list').hide();
				$("#job_status_list").hide();
				$.ajax({
					type : 'POST',
					url : sAjaxPath,
					dataType : 'json',
					data: {
						get_type : "admin_get_job_list"
					},
					success : function(data){
					
						$('#status_message').removeClass().addClass((data.error === true) ? 'error' : 'success');
						
						$("#job_list").text('');
						$("#job_list").append("<h2>Jobs</h2>");
						var sHTMLHeader = "<tr class='header'><td width='140'>Status</td><td width='140'>Jobname</td><td width='130'>Interval</td><td width='130'>Last # mined</td><td width='130'>Mined ago</td><td width='180'>State (current/last)</td><td width='150'>Hostname</td><td width='50'> </td><td width='50'> </td><td width='50'> </td></tr>";
						$("#job_list").append(sHTMLHeader);
						
						if (data.msg === null) {
						} else {
							$.each(data.msg,function(i,jobs) {
							sHTML = "";
								if (jobs.job_approved == 0) {
									sHTML = "<tr><td class='disabled'>" + jobs.description + "</td><td>" + jobs.job_name + "</td><td>" + jobs.job_run_interval_minutes + "</td><td>" + jobs.items_mined + "</td><td>" + jobs.mined_min_ago + "</td><td width='180'>" + jobs.nav_current_state_index + " / " + jobs.nav_last_state_index + "</td><td width='150'>" + jobs.host_name + "</td><td width='50'><a href='javascript:void(0);' onClick='JobStatus(" + jobs.job_id + ");'>status</a></td><td width='50'><a href='javascript:void(0);' onClick='EditJobSettings(" + jobs.job_id + ");'>edit</a></td><td width='50'><a href='javascript:void(0);' onClick='DeleteJob(" + jobs.job_id + ");'>delete</a></td></tr>";
								}
								else if (jobs.job_status_id == 1 && jobs.job_force_restart == 1) {
									sHTML = "<tr><td class='forced_restart'>Forced restart</td><td>" + jobs.job_name + "</td><td>" + jobs.job_run_interval_minutes + "</td><td>" + jobs.items_mined + "</td><td>" + jobs.mined_min_ago + "</td><td width='180'>" + jobs.nav_current_state_index + " / " + jobs.nav_last_state_index + "</td><td width='150'>" + jobs.host_name + "</td><td width='50'><a href='javascript:void(0);' onClick='JobStatus(" + jobs.job_id + ");'>status</a></td><td width='50'><a href='javascript:void(0);' onClick='EditJobSettings(" + jobs.job_id + ");'>edit</a></td><td width='50'><a href='javascript:void(0);' onClick='DeleteJob(" + jobs.job_id + ");'>delete</a></td></tr>";
								}
								else if (jobs.job_status_id == 1 && jobs.job_force_restart == 0) {
									sHTML = "<tr><td class='queuing'>" + jobs.start_run_in + "</td><td>" + jobs.job_name + "</td><td>" + jobs.job_run_interval_minutes + "</td><td>" + jobs.items_mined + "</td><td>" + jobs.mined_min_ago + "</td><td width='180'>" + jobs.nav_current_state_index + " / " + jobs.nav_last_state_index + "</td><td width='150'>" + jobs.host_name + "</td><td width='50'><a href='javascript:void(0);' onClick='JobStatus(" + jobs.job_id + ");'>status</a></td><td width='50'><a href='javascript:void(0);' onClick='EditJobSettings(" + jobs.job_id + ");'>edit</a></td><td width='50'><a href='javascript:void(0);' onClick='DeleteJob(" + jobs.job_id + ");'>delete</a></td></tr>";
								}
								else if (jobs.job_status_id == 2) {
									sHTML = "<tr><td class='running'>" + jobs.description + "</td><td>" + jobs.job_name + "</td><td>" + jobs.job_run_interval_minutes + "</td><td>" + jobs.items_mined + "</td><td>" + jobs.mined_min_ago + "</td><td width='180'>" + jobs.nav_current_state_index + " / " + jobs.nav_last_state_index + "</td><td width='150'>" + jobs.host_name + "</td><td width='50'><a href='javascript:void(0);' onClick='JobStatus(" + jobs.job_id + ");'>status</a></td><td width='50'><a href='javascript:void(0);' onClick='EditJobSettings(" + jobs.job_id + ");'>edit</a></td><td width='50'><a href='javascript:void(0);' onClick='DeleteJob(" + jobs.job_id + ");'>delete</a></td></tr>";
								} 
								$("#job_list").append(sHTML);
							});
						}
						$('#job_list').show(200);
						if (data.error === true) {
							$('#status_message').text(data.msg);
							$('#status_message').show(200);
						}
					},
					error : function(XMLHttpRequest, textStatus, errorThrown) {
						$('#status_message').removeClass().addClass('error')
							.text('There was an error.').show(200);
						$('#status_message').show(200);
					}
				});
			};
			
			function EditJobSettings(nJobId) {
				$("#create").val("Edit");
				$("#create_link").show();
				$("#job_status_list").hide();
				$("#form_job_container").hide(0);
				$.ajax({
					type : 'POST',
					url : sAjaxPath,
					dataType : 'json',
					data: {
						get_type : "admin_get_search_form_to_edit_job",
						job_id : nJobId
					},
					success : function(data){

						$('#status_message').removeClass().addClass((data.error === true) ? 'error' : 'success');
						$.each(data.msg,function(i,jobs){
						
							$("#job_id").val(jobs.job_id);
							$("#job_status_id").val(jobs.job_status_id);
							$("#job_name").val(jobs.job_name);
							$("#job_run_interval_minutes").val(jobs.job_run_interval_minutes);
							$("#script_path").val(jobs.script_path);
							$("#estimated_runtime").val(jobs.estimated_runtime);
							$("#parameters").val(jobs.parameters);
							$("#job_priority").val(jobs.job_priority);
							$("#job_force_restart").attr('checked', false);
						});
						$("#form_job_container").show(200);
						if (data.error === true)
							$("#status_message").show(200);
					},
					error : function(XMLHttpRequest, textStatus, errorThrown) {
						$('#status_message').removeClass().addClass('error')
							.text('There was an error.').show(200);
						$('#status_message').show(200);
					}
				});
			};
			
			function JobStatus(nJobId) {
				$("#job_status_list").text('');
				$.ajax({
					type : 'POST',
					url : sAjaxPath,
					dataType : 'json',
					data: {
						get_type : "admin_show_status_for_job",
						job_id : nJobId
					},
					success : function(data){

						$('#status_message').removeClass().addClass((data.error === true) ? 'error' : 'success');
						$("#job_status_list").text('');
						$("#job_status_list").append("<h2>Status</h2>");
						
						if (data.msg === null) {
						} else {
							$("#job_status_list").append("<table><tr><td>Status date</td><td>Description</td><td>Pages loaded</td><td>Items mined</td><td>Hostname</td></tr>");
							$.each(data.msg,function(i,jobs){
								$("#job_status_list").append("<tr><td>" + jobs.created_date + '</td><td>' + jobs.description + '</td><td>' + jobs.total_pages_loaded + '</td><td>' + jobs.items_mined + '</td><td>' + jobs.host_name + '</td></tr>');
							});
							$("#job_status_list").append("</table>");
						}
						
						if (data.error === true) {
							$('#status_message').text(data.msg);
							$("#status_message").show(200);
						} else {
							$("#job_status_list").show(200);
						}
					},
					error : function(XMLHttpRequest, textStatus, errorThrown) {
						$('#status_message').removeClass().addClass('error')
							.text('There was an error.').show(200);
						$('#status_message').show(200);
					}
				});
			};
			
			function DeleteJob(nJobId) {
				if (confirm("Er du sikker på at du vil slette dette job?")) { 
					$.ajax({
						type : 'POST',
						url : sAjaxPath,
						dataType : 'json',
						data: {
							get_type : "admin_delete_job",
							job_id : nJobId
						},
						success : function(data){

							$('#status_message').removeClass().addClass((data.error === true) ? 'error' : 'success');
							location.href = '<?php echo $_SERVER['PHP_SELF']; ?>';
							
						},
						error : function(XMLHttpRequest, textStatus, errorThrown) {
							$('#status_message').removeClass().addClass('error')
								.text('There was an error.').show(200);
							$('#status_message').show(200);
						}
					});
				}
			};
			
			getJobList();
		</script>
	</head>

	<body>
		<div class="page_content">
			<div id="status_message" style="display:none;"></div>

			<div id="form_job_container">
			<form>
			<input type="hidden" id="job_id" name="job_id" value="">
				
			<table cellspacing="0" cellpadding="0" border="0">
				
				<tr>
				<td class="label">&nbsp;</td>
				<td class="content" align="right">
					<input type="button" id="create" name="create" value=" Create ">
				</td>
				</tr>
				
				<tr><td class="header" colspan="2">Job</td></tr>
				
				<tr>
				<td class="label">Approved</td>
				<td class="content">
					<select id="job_status_id" name="job_status_id"><option value="3">False</option><option value="1">True</option></select>
					<input id="job_force_restart" name="job_force_restart" type="checkbox" onClick="$('#job_status_id').val(1);"><label for="job_force_restart">Force restart</label>
				</td>
				</tr>
				
				<tr>
				<td class="label">Jobname:</td>
				<td class="content">
					<input name="job_name" id="job_name" value="" size="50">
				</td>
				</tr>
				
				<tr>
				<td class="label">Script path:</td>
				<td class="content">
					<input name="script_path" id="script_path" value="scripts/airplay/AmazonCoUk/AmazonCoUk.php" size="50">
				</td>
				</tr>
				
				<tr>
				<td class="label">Parameters</td>
				<td class="content">
					<input name="parameters" id="parameters" value="" size="50">
				</td>
				</tr>
				
				<tr>
				<td class="label">Estimated runtime</td>
				<td class="content"><input name="estimated_runtime" id="estimated_runtime" value="1" size="5"></td>
				</tr>
				
				<tr style="display:none;">
				<td class="label">Priority</td>
				<td class="content">
					<select id="job_priority" name="job_priority"><option value="1">1</option><option value="2" selected="selected">2</option><option value="3">3</option>
					</select>(1 = daily, 2 = when ready, 3 = ikke lavet endnu)
				</td>
				</tr>
				
				<tr>
					<td class="content" colspan="2">
					&nbsp;
					</td>
				</tr>
				
				<tr>
				<td class="label">Interval:</td>
				<td class="content">
					<select name="job_run_interval_minutes" id="job_run_interval_minutes">
						<option value="1">Minute</option>
						<option value="60">Hourly</option>
						<option value="1440">Daily</option>
						<option value="10080" selected="selected">Weekly</option>
						<option value="20160">2 Weeks</option>
						<option value="30240">3 Weeks</option>
						<option value="43200">Monthly</option>
						<option value="86400">2 Month</option>
						<option value="525600">Yearly</option>
					</select>
				</td>
				</tr>
				
				<tr>
					<td class="content" colspan="2">
					&nbsp;
					</td>
				</tr>
				
				<tr>
				<td class="content" colspan="2">
					<a id="create_link" href="<?php echo $_SERVER['PHP_SELF']; ?>">Create new</a>
				</td>
				</tr>
				
			</table>

			</form>
			</div>
			
			<div id="job_list" style="display:none;"></div>

			<div id="job_status_list" style="display:none;"></div>
			
		</div>
	</body>

</html>