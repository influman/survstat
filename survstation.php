<?php
	$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>";      
	//***********************************************************************************************************************
	// V1.4 : Surveillance Station / Influman 2019
	//SYNO.API.Info
	$vInfo = 1;
	//SYNO.API.Auth
	$vAuth = 2;
	//SYNO.SurveillanceStation.Camera
	$vCamera = 6;
	//SYNO.SurveillanceStation.Camera.Event
	$vCameraEvent = 1;
	//SYNO.SurveillanceStation.ExternalRecording
	$vExternalRecording = 2;
	//SYNO.SurveillanceStation.PTZ
	$vPTZ = 1;
	// recuperation des infos depuis la requete
	$action = getArg("action", true, ''); 
	$server = getArg("server", true);
	$value = getArg("value", false);
	$camid_index = getArg("camid", false);
	$presetid = getArg("presetid", false);
	$ftp = getArg("ftp",false, '');
	// API DU PERIPHERIQUE APPELANT LE SCRIPT
    $periph_id = getArg('eedomus_controller_module_id'); 
	// Code erreur authentification
	$tab_error_auth = array(100 => "Unknown error", 101 => "The account parameter is not specified", 102 => "API does not exist", 103 => "Method does not exist",
						104 => "This API version is not supported", 105 => "Insufficient user privilege", 106 => "Connection time out", 107 => "Multiple login detected",
						400 => "Invalid password", 401 => "Guest or disabled account", 402 => "Permission denied", 403 => "One time password not specified",
						404 => "One time password authenticate failed");
	if ($action == '' ) {
		die();
	}
	
	$xml .= "<SURVSTATION>";
	$tab_param = explode(",",$server);
	$http = $tab_param[0];
	$server = $tab_param[1];
	$login = $tab_param[2];
	$pass = utf8_decode($tab_param[3]);
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
			$xml .= "<STATUS>Error code API ".$return_api['error']['code']." ".$tab_error_auth[$return_api['error']['code']]."</STATUS>";
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
	$url_auth = $http."://".$server."/webapi/".$auth_path."?api=SYNO.API.Auth&method=Login&version=".$vAuth."&account=".$login."&passwd=".urlencode($pass)."&session=SurveillanceStation&format=sid";
	$result_auth = httpQuery($url_auth, 'GET');
	$return_auth = sdk_json_decode($result_auth);
	$test_success = $return_auth['success'];
	if($test_success != 1){
		$xml .= "<STATUS>Authentication error ".$return_auth['error']['code']." ".$tab_error_auth[$return_auth['error']['code']];
		$xml .= "</STATUS>";
		//(passwords with special character not supported)</STATUS>";
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
			$tab_cam = array();
			$index = 0;
			foreach($return_cam['data']['cameras'] as $cam){
				$id_cam = $cam['id'];
				$index++;
				$name_cam = $cam['name'];
				$model_cam = $cam['model'];
				$statut_cam = $cam['camStatus'];
				$host_cam = $cam['host'].":".$cam['port'];
				$resolution = $cam['resolution'];
				$ptz = "";
				if ($cam['ptzCap'] != 0) {
					$ptz = " PTZ";
				}
				if ($cam['enabled'] != 1) {
					$statut_cam .= "-Disabled";
					if ($listcam == "") {
						$listcam = $index."(x)";
					} else {
						$listcam .= " | ".$index."(x)";
					}
				} else {
					if ($listcam == "") {
						$listcam = $index."(".$id_cam.")";
					} else {
						$listcam .= " | ".$index."(".$id_cam.")";
					}
				}
				$xml .= "<CAM_".$index.">ID ".$id_cam." - ".$name_cam." ".$resolution.$ptz." (".$statut_cam.") ".$host_cam." ".$model_cam."</CAM_".$index.">";
				$tab_cam[$index] = $id_cam;
			}
			$status = "Connected - ".$nbcam." cameras ".$listcam;
			$xml .= "<STATUS>".$status."</STATUS>";
			saveVariable("SURVSTATION_CAMID", $tab_cam);
		}
		
		// Transfo camid
		if ($camid_index != "" && is_numeric($camid_index)) {
			$tab_cam = loadVariable("SURVSTATION_CAMID");
			if (array_key_exists($camid_index, $tab_cam)) {
				$camid = $tab_cam[$camid_index];
			} else {
				$camid = $camid_index;
			}
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
			$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.Camera";
			$result_api = httpQuery($url_api, 'GET');
			$return_api = sdk_json_decode($result_api);
			$path = $return_api['data']['SYNO.SurveillanceStation.Camera']['path'];
			
			if ($value == "allstop") {
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
			if ($value == "alldisable") {
				$url_cam = $http."://".$server."/webapi/".$path."?privCamType=3&version=".$vCamera."&blIncludeDeletedCam=false&streamInfo=false&api=SYNO.SurveillanceStation.Camera&basic=true&method=List&_sid=".$sid;
				$result_cam = httpQuery($url_cam, 'GET');
				$return_cam = sdk_json_decode($result_cam);
				$list_enable = "";
				foreach($return_cam['data']['cameras'] as $cam){
					$id_cam = $cam['id'];
					if($cam['enabled'] == 1 ) {
						if ($list_enable != "") {
							$list_enable .= ",";
						}
						$list_enable .= $id_cam;
					}
				}
				if ($list_enable != "") {
					$url_dis = $http."://".$server."/webapi/".$path."?version=".$vCamera."&api=SYNO.SurveillanceStation.Camera&method=Disable&_sid=".$sid."&cameraIds=".$list_enable;
					$result_dis = httpQuery($url_dis, 'GET');
				}
				$xml .= $result_dis;
			}
			if ($value == "allenable") {
				$url_cam = $http."://".$server."/webapi/".$path."?privCamType=3&version=".$vCamera."&blIncludeDeletedCam=false&streamInfo=false&api=SYNO.SurveillanceStation.Camera&basic=true&method=List&_sid=".$sid;
				$result_cam = httpQuery($url_cam, 'GET');
				$return_cam = sdk_json_decode($result_cam);
				$list_disable = "";
				foreach($return_cam['data']['cameras'] as $cam){
					$id_cam = $cam['id'];
					if($cam['enabled'] != 1 ) {
						if ($list_disable != "") {
							$list_disable .= ",";
						}
						$list_disable .= $id_cam;
					}
				}
				if ($list_disable != "") {
					$url_ena = $http."://".$server."/webapi/".$path."?version=".$vCamera."&api=SYNO.SurveillanceStation.Camera&method=Enable&_sid=".$sid."&cameraIds=".$list_disable;
					$result_ena = httpQuery($url_ena, 'GET');
				}
				$xml .= $result_dis;
			}
			if ($value == "disable") {
				$url_dis = $http."://".$server."/webapi/".$path."?version=".$vCamera."&api=SYNO.SurveillanceStation.Camera&method=Disable&_sid=".$sid."&cameraIds=".$camid;
				$result_dis = httpQuery($url_dis, 'GET');
				$return_dis = sdk_json_decode($result_dis);
				if($return_dis['success'] != 1){
					if($return_dis['success'] == 0){
						$xml .= "<STATUS>Error Camera code ".$return_dis['error']['code']." ".$tab_error_auth[$return_dis['error']['code']]."</STATUS>";
					} else {
						$xml .= $result_dis;
					}
				} else {
					$xml .= $result_dis;
				}
				
			}
			if ($value == "enable") {
				$url_ena = $http."://".$server."/webapi/".$path."?version=".$vCamera."&api=SYNO.SurveillanceStation.Camera&method=Enable&_sid=".$sid."&cameraIds=".$camid;
			    $result_ena = httpQuery($url_ena, 'GET');
				$return_ena = sdk_json_decode($result_ena);
				if($return_ena['success'] != 1){
					if($return_ena['success'] == 0){
						$xml .= "<STATUS>Error Camera code ".$return_ena['error']['code']." ".$tab_error_auth[$return_ena['error']['code']]."</STATUS>";
					} else {
						$xml .= $result_ena;
					}
				} else {
					$xml .= $result_ena;
				}
			}
			if ($value == "allmddisable") {
				$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.Camera.Event";
				$result_api = httpQuery($url_api, 'GET');
				$return_api = sdk_json_decode($result_api);
				$pathevent = $return_api['data']['SYNO.SurveillanceStation.Camera.Event']['path'];
				$url_cam = $http."://".$server."/webapi/".$path."?privCamType=3&version=".$vCamera."&blIncludeDeletedCam=false&streamInfo=false&api=SYNO.SurveillanceStation.Camera&basic=true&method=List&_sid=".$sid;
				$result_cam = httpQuery($url_cam, 'GET');
				$return_cam = sdk_json_decode($result_cam);
				$list_enable = "";
				foreach($return_cam['data']['cameras'] as $cam){
					$id_cam = $cam['id'];
					if($cam['enabled'] == 1 ) {
						$url_dis = $http."://".$server."/webapi/".$pathevent."?version=".$vCameraEvent."&api=SYNO.SurveillanceStation.Camera.Event&method=MDParamSave&keep=true&source=-1&_sid=".$sid."&camId=".$id_cam;
						$result_dis = httpQuery($url_dis, 'GET');
					}
				}
				$xml .= $result_dis;
			}
			if ($value == "allmdenable") {
				$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.Camera.Event";
				$result_api = httpQuery($url_api, 'GET');
				$return_api = sdk_json_decode($result_api);
				$pathevent = $return_api['data']['SYNO.SurveillanceStation.Camera.Event']['path'];
				$url_cam = $http."://".$server."/webapi/".$path."?privCamType=3&version=".$vCamera."&blIncludeDeletedCam=false&streamInfo=false&api=SYNO.SurveillanceStation.Camera&basic=true&method=List&_sid=".$sid;
				$result_cam = httpQuery($url_cam, 'GET');
				$return_cam = sdk_json_decode($result_cam);
				$list_enable = "";
				foreach($return_cam['data']['cameras'] as $cam){
					$id_cam = $cam['id'];
					if($cam['enabled'] == 1 ) {
						$url_ena = $http."://".$server."/webapi/".$pathevent."?version=".$vCameraEvent."&api=SYNO.SurveillanceStation.Camera.Event&method=MDParamSave&keep=true&source=1&_sid=".$sid."&camId=".$id_cam;
						$result_ena = httpQuery($url_ena, 'GET');
					}
				}
				$xml .= $result_ena;
			}
			if ($value == "mddisable") {
				$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.Camera.Event";
				$result_api = httpQuery($url_api, 'GET');
				$return_api = sdk_json_decode($result_api);
				$pathevent = $return_api['data']['SYNO.SurveillanceStation.Camera.Event']['path'];
				$url_dis = $http."://".$server."/webapi/".$pathevent."?version=".$vCameraEvent."&api=SYNO.SurveillanceStation.Camera.Event&method=MDParamSave&keep=true&source=-1&_sid=".$sid."&camId=".$camid;
				$result_dis = httpQuery($url_dis, 'GET');
				$return_dis = sdk_json_decode($result_dis);
				if($return_dis['success'] != 1){
					if($return_dis['success'] == 0){
						$xml .= "<STATUS>Error Camera Event code ".$return_dis['error']['code']." ".$tab_error_auth[$return_dis['error']['code']]."</STATUS>";
					} else {
						$xml .= $result_dis;
					}
				} else {
					$xml .= $result_dis;
				}
			}
			if ($value == "mdenable") {
				$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.Camera.Event";
				$result_api = httpQuery($url_api, 'GET');
				$return_api = sdk_json_decode($result_api);
				$pathevent = $return_api['data']['SYNO.SurveillanceStation.Camera.Event']['path'];
				$url_ena = $http."://".$server."/webapi/".$pathevent."?version=".$vCameraEvent."&api=SYNO.SurveillanceStation.Camera.Event&method=MDParamSave&keep=true&source=1&_sid=".$sid."&camId=".$camid;
				$result_ena = httpQuery($url_ena, 'GET');
				$return_ena = sdk_json_decode($result_ena);
				if($return_ena['success'] != 1){
					if($return_ena['success'] == 0){
						$xml .= "<STATUS>Error Camera Event code ".$return_ena['error']['code']." ".$tab_error_auth[$return_ena['error']['code']]."</STATUS>";
					} else {
						$xml .= $result_ena;
					}
				} else {
					$xml .= $result_ena;
				}
			}
		}
		// PTZ status
		if ($action == "ptzstatus") {
			// Path de SYNO.SurveillanceStation.Camera
			$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.Camera";
			$result_api = httpQuery($url_api, 'GET');
			$return_api = sdk_json_decode($result_api);
			$path = $return_api['data']['SYNO.SurveillanceStation.Camera']['path'];
			$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.PTZ";
			$result_api = httpQuery($url_api, 'GET');
			$return_api = sdk_json_decode($result_api);
			$ptzpath = $return_api['data']['SYNO.SurveillanceStation.PTZ']['path'];
			// Accès SYNO.SurveillanceStation.Camera pour liste détaillées des caméras
			$url_cam = $http."://".$server."/webapi/".$path."?privCamType=3&version=".$vCamera."&blIncludeDeletedCam=false&streamInfo=false&api=SYNO.SurveillanceStation.Camera&basic=true&method=List&_sid=".$sid;
			$result_cam = httpQuery($url_cam, 'GET');
			$return_cam = sdk_json_decode($result_cam);
			$ptzcap = false;
			$list_cam = array();
			$i = 0;
			$index = 0;
			foreach($return_cam['data']['cameras'] as $cam){
				$index++;
				$id_cam = $cam['id'];
				$nb_preset = $cam['presetNum'];
				if ($cam['ptzCap'] != 0) {
					$ptzcap = true;
					if ($cam['enabled'] == 1) {
						$list_cam[$i]['id'] = $id_cam;
						$list_cam[$i]['index'] = $index;
						$presets = "";
						$presets_ids = "";
						// Accès SYNO.SurveillanceStation.PTZ pour liste détaillées des presets
						$url_ptz = $http."://".$server."/webapi/".$ptzpath."?version=".$vPTZ."&api=SYNO.SurveillanceStation.PTZ&method=ListPreset&cameraId=".$id_cam."&_sid=".$sid;
						$result_ptz = httpQuery($url_ptz, 'GET');
						$return_ptz = sdk_json_decode($result_ptz);
						foreach($return_ptz['data']['presets'] as $preset){
							$id_preset = $preset['id'];
							$name_preset = $preset['name'];
							$presets_ids .= $id_preset."|";
							$presets .= $name_preset."(".$id_preset.") | ";
						}
						$list_cam[$i]['presets'] = $presets_ids;
						$xml .= "<CAM_".$index.">ID ".$id_cam." - ".$nb_preset." presets ".$presets."</CAM_".$index.">";
						$i++;
					}
				}
			}
			if ($ptzcap) {
				$status = "PTZ ";
				foreach($list_cam as $cam) {
					if ($cam['presets'] == "") {
						$status .= "Cam ".$cam['index']."(".$cam['id'].")"." +*";
					} else {
						$status .= "Cam ".$cam['index']."(".$cam['id'].") > ".$cam['presets']." +*";
					}
				}
			} else {
				$status = "PTZ not available";
			}
			$xml .= "<PTZSTATUS>".$status."</PTZSTATUS>";
		}
		// PTZ controle
		if ($action == "controlptz") {
			$url_api = $http."://".$server."/webapi/query.cgi?api=SYNO.API.Info&method=Query&version=".$vInfo."&query=SYNO.SurveillanceStation.PTZ";
			$result_api = httpQuery($url_api, 'GET');
			$return_api = sdk_json_decode($result_api);
			$ptzpath = $return_api['data']['SYNO.SurveillanceStation.PTZ']['path'];
			if ($value == "gopreset") {
				$url_ptz = $http."://".$server."/webapi/".$ptzpath."?version=".$vPTZ."&api=SYNO.SurveillanceStation.PTZ&method=GoPreset&cameraId=".$camid."&presetId=".$presetid."&_sid=".$sid;
				$result_ptz = httpQuery($url_ptz, 'GET');
			}
			if ($value == "moveup") {
				$url_ptz = $http."://".$server."/webapi/".$ptzpath."?version=".$vPTZ."&api=SYNO.SurveillanceStation.PTZ&method=Move&direction=up&speed=1&cameraId=".$camid."&_sid=".$sid;
				$result_ptz = httpQuery($url_ptz, 'GET');
			}
			if ($value == "movedown") {
				$url_ptz = $http."://".$server."/webapi/".$ptzpath."?version=".$vPTZ."&api=SYNO.SurveillanceStation.PTZ&method=Move&direction=down&speed=1&cameraId=".$camid."&_sid=".$sid;
				$result_ptz = httpQuery($url_ptz, 'GET');
			}
			if ($value == "moveleft") {
				$url_ptz = $http."://".$server."/webapi/".$ptzpath."?version=".$vPTZ."&api=SYNO.SurveillanceStation.PTZ&method=Move&direction=left&speed=1&cameraId=".$camid."&_sid=".$sid;
				$result_ptz = httpQuery($url_ptz, 'GET');
			}
			if ($value == "moveright") {
				$url_ptz = $http."://".$server."/webapi/".$ptzpath."?version=".$vPTZ."&api=SYNO.SurveillanceStation.PTZ&method=Move&direction=right&speed=1&cameraId=".$camid."&_sid=".$sid;
				$result_ptz = httpQuery($url_ptz, 'GET');
			}
			if ($value == "zoomin") {
				$url_ptz = $http."://".$server."/webapi/".$ptzpath."?version=".$vPTZ."&api=SYNO.SurveillanceStation.PTZ&method=Zoom&control=in&moveType=Start&cameraId=".$camid."&_sid=".$sid;
				$result_ptz = httpQuery($url_ptz, 'GET');
			}
			if ($value == "zoomout") {
				$url_ptz = $http."://".$server."/webapi/".$ptzpath."?version=".$vPTZ."&api=SYNO.SurveillanceStation.PTZ&method=Zoom&control=in&moveType=Start&cameraId=".$camid."&_sid=".$sid;
				$result_ptz = httpQuery($url_ptz, 'GET');
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
