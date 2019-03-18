<?php
	$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>";      
	//***********************************************************************************************************************
	// V1.0 : Surveillance Station / Influman 2019
	//SYNO.API.Info
	$vInfo = 1;
	//SYNO.API.Auth
	$vAuth = 2;
	//SYNO.SurveillanceStation.Camera
	$vCamera = 6;
	//SYNO.SurveillanceStation.ExternalRecording
	$vExternalRecording = 2;
	// recuperation des infos depuis la requete
	$action = getArg("action", true, ''); 
	$server = getArg("server", true);
	$value = getArg("value", false);
	$camid = getArg("camid", false);
	$ftp = getArg("ftp",false, '');
	// API DU PERIPHERIQUE APPELANT LE SCRIPT
    $periph_id = getArg('eedomus_controller_module_id'); 
	
	if ($action == '' ) {
		die();
	}
	$xml .= "<SURVSTATION>";
	$tab_param = explode(",",$server);
	$http = $tab_param[0];
	$server = $tab_param[1];
	$login = $tab_param[2];
	$pass = $tab_param[3];
	$ftpok = false;
	if ($ftp != '') {
		$tab_param = explode(",",$ftp);
		$ftp_server = $tab_param[0];
		$ftp_user = $tab_param[1];
		$ftp_pass = $tab_param[2];
		$ftpok = true;
	}
	
	
	// Accès au Surveillance Station
	//Get SYNO.API.Auth Path (recommended by Synology for further update)
    $url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.API.Auth";
	$result_api = httpQuery($url_api, 'GET');
	$return_api = sdk_json_decode($result_api);
	$test_success = $return_api['success'];
	if($test_success != 1){
		if($test_sucess == 0){
			$xml .= "<STATUS>Error code API ".$return_api['error']['code']."</STATUS>";
		} else {
			$xml .= "<STATUS>Host access error ".$result_api."</STATUS>";
		}
		$xml .= "</SURVSTATION>";
		sdk_header('text/xml');
		echo $xml;
		die();
	}
    $path = $return_api['data']['SYNO.API.Auth']['path'];
	$auth_path = $path;
	// Login and creating sid
	$url_auth = $http."://".$server."/webapi/".$auth_path."?api=SYNO.API.Auth&method=Login&version=".$vAuth."&account=".$login."&passwd=".$pass."&session=SurveillanceStation&format=sid";
	$result_auth = httpQuery($url_auth, 'GET');
	$return_auth = sdk_json_decode($result_auth);
	$test_success = $return_auth['success'];
	if($test_success != 1){
		$xml .= "<STATUS>Authentication error ".$return_auth['error']['code']." (passwords with special character not supported)</STATUS>";
	} else {
		//authentication successful
		$sid = $return_auth['data']['sid']; // Code de session
		// core
		if ($action == "shutdown" || $action == "reboot" || $action == "reset") {
			$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.Core.System";
			$result_api = httpQuery($url_api, 'GET');
			$return_api = sdk_json_decode($result_api);
			$path = $return_api['data']['SYNO.Core.System']['path'];
			$url_shut = $http."://".$server."/webapi/".$path."?api=SYNO.Core.System&force=false&local=true&method=".$action."&version=1&_sid=".$sid;
			$result_shut = httpQuery($url_shut, 'GET');
			$xml .= $result_shut;
		}
		// Monitoring
		if ($action == "monitoring") {
			// Path de SYNO.Core.System.Utilization
			$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.Core.System.Utilization";
			$result_api = httpQuery($url_api, 'GET');
			$return_api = sdk_json_decode($result_api);
			$path = $return_api['data']['SYNO.Core.System.Utilization']['path'];
			// Accès SYNO.Core.System.Utilization pour performance du NAS
			$url_core = $http."://".$server."/webapi/".$path."?api=SYNO.Core.System.Utilization&method=get&version=1&type=current&_sid=".$sid;
			$result_core = httpQuery($url_core, 'GET');
			$return_core = sdk_json_decode($result_core);
			if (is_numeric($return_core['data']['cpu']['system_load'])) {
				$cpu = $return_core['data']['cpu']['system_load'] + $return_core['data']['cpu']['user_load'] + $return_core['data']['cpu']['other_load'];
				$ram = $return_core['data']['memory']['real_usage'];
				$rx = round($return_core['data']['network'][0]['rx'] / 1024, 2);
				$tx = round($return_core['data']['network'][0]['tx'] / 1024, 2);
			} else {
				$cpu = "--";
				$ram = "--";
				$rx = "--";
				$tx = "--";
			}
			$xml .= "<CPU>".$cpu." %</CPU>";
			$xml .= "<RAM>".$ram." %</RAM>";
			$xml .= "<LAN>Rx ".$rx." KB/s | Tx ".$tx." KB/s</LAN>";
		}
		// status
		if ($action == "getstatus") {
			// Path de SYNO.SurveillanceStation.Info
			$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.Info";
			$result_api = httpQuery($url_api, 'GET');
			$return_api = sdk_json_decode($result_api);
			$path = $return_api['data']['SYNO.SurveillanceStation.Info']['path'];
			// Accès SYNO.SurveillanceStation.Info pour version et nombre de caméras
			$url_info = $http."://".$server."/webapi/".$path."?api=SYNO.SurveillanceStation.Info&method=GetInfo&version=".$vAuth."&_sid=".$sid;
			$result_info = httpQuery($url_info, 'GET');
			$return_info = sdk_json_decode($result_info);
			$version = $return_info['data']['version']['major'].".".$return_info['data']['version']['minor']." build ".$return_info['data']['version']['build'];
			$xml .= "<VERSION>".$version."</VERSION>";
			$nbcam = $return_info['data']['cameraNumber'];
			$xml .= "<NBCAM>".$nbcam."</NBCAM>";
			// Path de SYNO.SurveillanceStation.Camera
			$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.Camera";
			$result_api = httpQuery($url_api, 'GET');
			$return_api = sdk_json_decode($result_api);
			$path = $return_api['data']['SYNO.SurveillanceStation.Camera']['path'];
			// Accès SYNO.SurveillanceStation.Camera pour liste détaillées des caméras
			$url_cam = $http."://".$server."/webapi/".$path."?privCamType=3&version=".$vCamera."&blIncludeDeletedCam=false&streamInfo=false&api=SYNO.SurveillanceStation.Camera&basic=true&method=List&_sid=".$sid;
			$result_cam = httpQuery($url_cam, 'GET');
			$return_cam = sdk_json_decode($result_cam);
			$listcam = "";
			foreach($return_cam['data']['cameras'] as $cam){
				$id_cam = $cam['id'];
				$name_cam = $cam['name'];
				$model_cam = $cam['model'];
				$statut_cam = $cam['camStatus'];
				if ($cam['enabled'] != 1) {
					$statut_cam .= "-Disabled";
					$listcam .= "|x";
				} else {
					$listcam .= "|".$id_cam;
				}
				$xml .= "<CAM_ID_".$id_cam.">".$name_cam." (".$statut_cam.") ".$model_cam."</CAM_ID_".$id_cam.">";
			}
			$status = "Connected - ".$nbcam." cameras ".$listcam;
			$xml .= "<STATUS>".$status."</STATUS>";
		}
		// snapshot
		if ($action == "showsnap") {
			// Obtention d'un snap
			$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.Camera";
			$result_api = httpQuery($url_api, 'GET');
			$return_api = sdk_json_decode($result_api);
			$path = $return_api['data']['SYNO.SurveillanceStation.Camera']['path'];
			$url_snap = $http."://".$server."/webapi/".$path."?api=SYNO.SurveillanceStation.Camera&method=GetSnapshot&camStm=1&preview=true&version=".$vCamera."&cameraId=".$camid."&_sid=".$sid;
			$result_snap = httpQuery($url_snap, 'GET');
			//logout
			$url_logout = $http."://".$server."/webapi/".$auth_path."?api=SYNO.API.Auth&method=Logout&version=".$vAuth."&session=SurveillanceStation&_sid=".$sid;
			$result_logout = httpQuery($url_logout, 'GET');
			sdk_header('image/jpg');
			echo $result_snap;
			die();
		}
		// ftp snapshot
		if ($action == "snapftp") {
			// Obtention d'un snap
			$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.Camera";
			$result_api = httpQuery($url_api, 'GET');
			$return_api = sdk_json_decode($result_api);
			$path = $return_api['data']['SYNO.SurveillanceStation.Camera']['path'];
			$url_snap = $http."://".$server."/webapi/".$path."?api=SYNO.SurveillanceStation.Camera&method=GetSnapshot&camStm=1&preview=true&version=".$vCamera."&cameraId=".$camid."&_sid=".$sid;
			$result_snap = httpQuery($url_snap, 'GET');
			// enregistrement FTP
			if ($ftpok) {
				$return = ftpUpload($ftp_server, $ftp_user, $ftp_pass, $result_snap, 'camera_'.$camid.'_snap.jpg');
			}
		}
		// Cameras
		if ($action == "controlcam") {
			if ($value == "allstop") {
				$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.Camera";
				$result_api = httpQuery($url_api, 'GET');
				$return_api = sdk_json_decode($result_api);
				$path = $return_api['data']['SYNO.SurveillanceStation.Camera']['path'];
				$url_cam = $http."://".$server."/webapi/".$path."?privCamType=3&version=".$vCamera."&blIncludeDeletedCam=false&streamInfo=false&api=SYNO.SurveillanceStation.Camera&basic=true&method=List&_sid=".$sid;
				$result_cam = httpQuery($url_cam, 'GET');
				$return_cam = sdk_json_decode($result_cam);
				$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.ExternalRecording";
				$result_api = httpQuery($url_api, 'GET');
				$return_api = sdk_json_decode($result_api);
				$path = $return_api['data']['SYNO.SurveillanceStation.ExternalRecording']['path'];
				foreach($return_cam['data']['cameras'] as $cam){
					$id_cam = $cam['id'];
					if($cam['enabled'] == 1 ) {
						$url_stop = $http."://".$server."/webapi/".$path."?api=SYNO.SurveillanceStation.ExternalRecording&method=Record&version=".$vExternalRecording."&cameraId=".$id_cam."&action=stop&_sid=".$sid;
						$result_stop = httpQuery($url_stop, 'GET');
					}
				}
				$xml .= $result_stop;
			}
			if ($value == "allstart") {
				$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.Camera";
				$result_api = httpQuery($url_api, 'GET');
				$return_api = sdk_json_decode($result_api);
				$path = $return_api['data']['SYNO.SurveillanceStation.Camera']['path'];
				$url_cam = $http."://".$server."/webapi/".$path."?privCamType=3&version=".$vCamera."&blIncludeDeletedCam=false&streamInfo=false&api=SYNO.SurveillanceStation.Camera&basic=true&method=List&_sid=".$sid;
				$result_cam = httpQuery($url_cam, 'GET');
				$return_cam = sdk_json_decode($result_cam);
				$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.ExternalRecording";
				$result_api = httpQuery($url_api, 'GET');
				$return_api = sdk_json_decode($result_api);
				$path = $return_api['data']['SYNO.SurveillanceStation.ExternalRecording']['path'];
				foreach($return_cam['data']['cameras'] as $cam){
					$id_cam = $cam['id'];
					if($cam['enabled'] == 1) {
						$url_start = $http."://".$server."/webapi/".$path."?api=SYNO.SurveillanceStation.ExternalRecording&method=Record&version=".$vExternalRecording."&cameraId=".$id_cam."&action=start&_sid=".$sid;
						$result_start = httpQuery($url_start, 'GET');
					}
				}
				$xml .= $result_start;
			}
			if ($value == "stop") {
				$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.ExternalRecording";
				$result_api = httpQuery($url_api, 'GET');
				$return_api = sdk_json_decode($result_api);
				$path = $return_api['data']['SYNO.SurveillanceStation.ExternalRecording']['path'];
				$url_stop = $http."://".$server."/webapi/".$path."?api=SYNO.SurveillanceStation.ExternalRecording&method=Record&version=".$vExternalRecording."&cameraId=".$camid."&action=stop&_sid=".$sid;
				$result_stop = httpQuery($url_stop, 'GET');
				$xml .= $result_stop;
			}
			if ($value == "start") {
				$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.ExternalRecording";
				$result_api = httpQuery($url_api, 'GET');
				$return_api = sdk_json_decode($result_api);
				$path = $return_api['data']['SYNO.SurveillanceStation.ExternalRecording']['path'];
				$url_start = $http."://".$server."/webapi/".$path."?api=SYNO.SurveillanceStation.ExternalRecording&method=Record&version=".$vExternalRecording."&cameraId=".$camid."&action=start&_sid=".$sid;
				$result_start = httpQuery($url_start, 'GET');
				$xml .= $result_start;
			}
		}
		//logout
		$url_logout = $http."://".$server."/webapi/".$auth_path."?api=SYNO.API.Auth&method=Logout&version=".$vAuth."&session=SurveillanceStation&_sid=".$sid;
		$result_logout = httpQuery($url_logout, 'GET');
	}
	$xml .= "</SURVSTATION>";
	sdk_header('text/xml');
	echo $xml;                      
?>
