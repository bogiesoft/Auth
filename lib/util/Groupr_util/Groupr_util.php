<?php
require_once(dirname(__FILE__) . "/../util.php");

/**
 * @author morgan
 */
class Groupr_util extends util {
	private static $config;
	
	/**
	 * Initialise utility
	 */
	public static function init() {
		self::$util_name = "Groupr";
		self::verifyEnabled();
		self::$config = Auth::getConfig(self::$util_name);
		Auth::loadClass("AccountOwner_api");
	}
	
	/**
	 * Load data for web interface
	 */
	public static function admin() {
		$data = array("current" => "Utility", "util" => self::$util_name, "template" => "main");
		$service_id = self::$config['service_id'];
		$service = Service_model::get($service_id);
		if(!$service) {
			throw new Exception("Service '$service_id' does not exist.");
		}
		
		if(isset($_POST['group_cn']) && isset($_POST['gname'])) {
			Auth::loadClass("PasswordGen");
			$group_cn = trim($_POST['group_cn']);
			if($group_cn == "") {
				$group_cn = trim($_POST['gname']);
			}
			if(!$group = UserGroup_model::get_by_group_cn($group_cn)) {
				$data['message'] = "Group $group_cn does not exist!";
				return $data;
			}
			$group -> populate_list_OwnerUserGroup();
			if(count($group -> list_OwnerUserGroup) == 0) {
				$data['message'] = "Group '$group_cn' has no direct members.";
			}
			
			$print = isset($_POST['print']);
			$good = $fail = 0;
			foreach($group -> list_OwnerUserGroup as $oug) {
				$preset = passwordGen::Generate();
				$account = Account_model::get_by_service_owner_unique($service_id, $oug -> owner_id);
				if ($account){
					AccountOwner_api::pwreset($oug -> AccountOwner -> owner_id, $preset, $print);
					$passwrd [$account -> account_login] = $preset;
					$good++;
				} else {
					$fail++;
				}
					
			}
			$data['message'] = "Of " . count($group -> list_OwnerUserGroup) . " users in $group_cn, $good have been reset, $fail had no account in $service_id.";
			$data['passwrd'] = $passwrd;
		}
		
		return $data;
	}
	
	/**
	 * Do any maintenance tasks
	 */
	public static function doMaintenance() {
		
		// Do tasks here
		throw new Exception("Unimplemented");
	}
}
