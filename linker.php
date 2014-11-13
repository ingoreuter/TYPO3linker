<?php
class linkCreator {

	public function renderFileList($path) {
		if(!$path) {
			$path = "";
		}

		$searchpath = realpath(__DIR__ . "/" . $path);

		$navigation = array();
		$subdir = array();
		$files = array();

		$objects = scandir($searchpath);

		foreach ($objects as $myObject) {
			$objectPath = $searchpath . "/" . $myObject;
			$objectInfo = array(
				'name' => $myObject,
				'path' => $path . "/" . $myObject,
			);
			if(is_dir($objectPath . "/" . $myObject) && $myObject == "..") {
				$objectInfo['name'] = "Go to parent directory";
				$navigation[] = $objectInfo;
				continue;
			}

			if(is_dir($objectPath . "/" . $myObject) && $myObject == ".") {
				$objectInfo['name'] = "Reload this directory";
				$navigation[] = $objectInfo;
				continue;
			}

			if(is_dir($objectPath)) {
				$subdir[] = $objectInfo;
			}
			else {
				$files[] = $objectInfo;
			}
		}

	return json_encode(array('folders' => $subdir, 'files' => $files, 'location' => $searchpath, 'navigation' => $navigation));
	}

	public function createLinks($path) {

		$status = array(
			'code' => false,
			'msg' => "",
		);

		// Set default directories
		$srcFolder = realpath(__DIR__) . "/typo3_src";
		$typo3Folder = realpath(__DIR__) . "/typo3";
		$indexPhpFile = realpath(__DIR__) . "/index.php";

		// Remove old index.php
		if(is_file($indexPhpFile)) {
			if(is_link($indexPhpFile)) {
				if(!@unlink($indexPhpFile)) $status['msg'] = "Unable to unlink old index.php file";
			}
			else {
				if(!@rename($indexPhpFile, __DIR__ . "/index_old" . time() . ".php")) $status['msg'] = "Unable to rename old index.php file";
			}
		}
		if($status['msg'] != "") return json_encode($status);

		// Remove old typo3 folder
		if(is_dir($typo3Folder)) {
			if(is_link($typo3Folder)) {
				if(!@unlink($typo3Folder) && @rmdir($typo3Folder)) $status['msg'] = "Unable to unlink old typo3 folder";
			}
			else {
				if(!@rename($typo3Folder, $typo3Folder . "_old" . time())) $status['msg'] = "Unable to rename old typo3 folder";
			}
		}
		if($status['msg'] != "") return json_encode($status);

		// Remove old src folder or symlink
		if(is_dir($srcFolder)) {
			if(is_link($srcFolder)) {
				if(!@unlink($srcFolder) && @rmdir($srcFolder)) $status['msg'] = "Unable to unlink old src folder";
			}
			elseif(is_dir($srcFolder)) {
				if(!@rename($srcFolder, $srcFolder . "_old" . time())) {
					$status['msg'] = "Unable to rename old src folder";
				}
			}
		}
		if($status['msg'] != "") return json_encode($status);


		// Create new links
		if(!@symlink(realpath($path) , "typo3_src")) {
			$status['msg'] = "Unable to create symlink to typo3 src folder. \n(Tried to link to: $path)";
		}
		else {
			if(!@symlink("typo3_src/typo3", "typo3")) $status['msg'] = "Unable to create symlink to typo3 folder";
			if(!@symlink("typo3_src/index.php", "index.php")) $status['msg'] = "Unable to create symlink to index.php file";
		}
		if($status['msg'] != "") {
			return json_encode($status);
		}
		else {
			$status['code'] = true;
			$status['msg'] = "Linking succeeded.";
			return json_encode($status);
		}
	}

	public function copyHtaccess() {
		$srcFile = realpath(__DIR__ . "/typo3_src/_.htaccess");
		$newFile = realpath(__DIR__)  . "/_.htaccess";


		if(is_file($newFile)) {
			if(!rename($newFile, $newFile . "_old" .time())) return "Error: Unable to rename old file.";
		}

		if(copy($srcFile, $newFile)) {
			return "_.htaccess copied successful.";
		}
		else {
			return "Error: Unable to copy _.htaccess file.\nSource: $srcFile \nDestination: $newFile";
		}
	}
}


/*
 * --------------
 * Page functions
 * --------------
 */


$linkCreator = new linkCreator();
if(array_key_exists('job', $_GET) && array_key_exists('ajax', $_GET)) {
	$content = "";
	switch ($_GET['job']) {
		case "scan":
			$content = $linkCreator->renderFileList($_GET['path']);
			break;
		case "link":
			$content = $linkCreator->createLinks($_GET['path']);
			break;
		case "copyHtaccess":
			$content = $linkCreator->copyHtaccess();
			break;
		default:
			$content = "Error. Nothing to do.";

	}
	echo $content;
	return;
}
?>

<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<title>TYPO3 symlink creator</title>
	<style>
		html {
			font-family: "Myriad Pro", "Myriad Web", "Tahoma", "Helvetica", "Arial", sans-serif;
			background-color: #e4e4e4;
		}
		a {
			color: inherit;
			text-decoration: inherit;
		}
		.contentbox {
			width: 600px;
			margin: 100px auto;
			background-color: white;
			padding: 40px;
		}

		h1 {
			border-bottom: 1px solid #FF8700;
			color: #FF8700;
		}

		h2 {
			color: #515151;
		}

		.location {
			margin-bottom: 20px;
		}

		#fileview {
			border-color: silver;
			border-style: solid;
			border-width: 1px 0 1px 0;

		}

		ul {
			list-style: none;
			margin: 0;
			padding: 0;
		}

		.filelist li {
			padding: 7px 10px;
			border-bottom: 1px solid #f1f1f1;
		}

		li:last-child {
			border: none;
		}

		.navigation {
			overflow: hidden;
			background-color: #f1f1f1;
			padding: 10px;
		}

		.navigation li {
			width: 50%;
			float: right;
			text-align: right;
		}

		.navigation li:last-child {
			text-align: left;
		}

		.filelist .folderlink {
			cursor: pointer;
			display: block;
		}

		.folderlink:hover {
			color: #FF8700;
		}

		.fileitem {
			color: #8C8C8C;
		}

		.fileitem:before {
			content: "[FILE] "
		}

		.loadingMSG {
			background-color: #FF8700;
			margin: 15px 0;
			padding: 10px;
			color: white;
		}

		.linkItButton {
			background-color: #FF8700;
			color: white;
			display: block;
			margin: 0px auto;
			padding: 14px;
			font-size: 1.4em;
			text-align: center;
			margin-top: 30px;
			width: 66%;
			cursor: pointer;
		}

		.linkItButton:hover {
			color: #FF8700;
			background-color: #515151;
		}


	</style>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script type="text/javascript">

		function scanDir(myPath) {
			var html = "";

			var oldHTML = $('#fileview').html();
			var loadingMSG = '<div class="loadingMSG">Loading...</div>';
			$('#fileview').html(oldHTML + loadingMSG);

			var ajaxRequest = $.ajax({
				url: <?php echo '"' . $_SERVER['PHP_SELF'] . '"';?>,
				type: "GET",
				data: {
					ajax: true,
					job: 'scan',
					path: myPath
				},
				dataType: "json"
			});
			ajaxRequest.done(function( content ) {
				html+= '<ul class="filelist">';
				$.each(content.folders, function(i, folder) {
					html +='<li class="folderitem"><a href="#" data-path="' + folder.path +'" class="folderlink">' + folder.name + '</a></li>';
				});
				$.each(content.files, function(i, file) {
					html +='<li class="fileitem">' + file.name + '</li>';
				});
				html += '</ul>';
				$('#fileview').html(html);

				html = '<ul class="navigation">';
				$.each(content.navigation, function(i, folder) {
					html +='<li class="navitem"><a href="#" data-path="' + folder.path +'" class="folderlink">' + folder.name + '</a></li>';
				});
				html += '</ul>';
				$('#navigation').html(html);

				$('#locationPath').html(content.location);
			});
			ajaxRequest.fail(function( jqXHR, textStatus ) {
				alert( "Request failed: " + textStatus );
			});
		}

		$(document).ready(function() {
			scanDir('');
			$("#fileview").on("click", "a", function(e) {
				e.preventDefault();
				var path = $(this).data('path');
				scanDir(path);
			});
			$("#navigation").on("click", "a", function(e) {
				e.preventDefault();
				var path = $(this).data('path');
				scanDir(path);
			});
			$('#theButton').on("click", function(e) {
				var path = $('#locationPath').html();
				if(window.confirm('Link source to "' + path + '"')) {
					var ajaxRequest = $.ajax({
						url: <?php echo '"' . $_SERVER['PHP_SELF'] . '"';?>,
						type: "GET",
						data: {
							ajax: true,
							job: 'link',
							path: path
						},
						dataType: "json"
					});
					ajaxRequest.done(function( content ) {
						if(content.code) {
							var copyHtaccess = window.confirm("Linking successful. \n\nWould you like to copy _htaccess template form new source directory?");
							 scanDir('');

							// copy _.htaccess file to root folder
							if(copyHtaccess) {
								var ajaxRequest = $.ajax({
									url: <?php echo '"' . $_SERVER['PHP_SELF'] . '"';?>,
									type: "GET",
									data: {
										ajax: true,
										job: 'copyHtaccess',
									},
									dataType: "text"
								});
								ajaxRequest.done(function( returnMsg ) {
									window.alert(returnMsg);

								});
								ajaxRequest.fail(function( jqXHR, textStatus ) {
									alert( "Request failed: " + textStatus );
								});
							}
						}
						else {
							window.alert("Linking failed.\nError: " + content.msg);
						}

					});
					ajaxRequest.fail(function( jqXHR, textStatus ) {
						alert( "Request failed: " + textStatus );
					});
				}
			});
		});
	</script>
</head>

<body>
	<div class="contentbox">
		<h1>TYPO3 symlink creator</h1>

		<h2>Open source folder</h2>
		<div class="location">You are here: <div id="locationPath"></div> </div>
		<div id="navigation"></div>
		<div id="fileview"></div>
		<div class="createLink">
			<span class="linkItButton" id="theButton">Link this folder as TYPO3 source</span>
		</div>
	</div>
</body>
</html>