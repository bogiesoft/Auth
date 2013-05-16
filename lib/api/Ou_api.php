<?php
 
/**
 * This class provides an interface for managing organizational units in the local database.
 * It ensures that changes are pushed onto the ActionQueue.
 * 
 * @author Michael Billington <michael.billington@gmail.com>
 */
class Ou_api {
	function init() {
		Auth::loadClass("Ou_model");
		Auth::loadClass("UserGroup_api");
		Auth::loadClass("AccountOwner_api");
		Auth::loadClass("ActionQueue_api");
	}

	/**
	 * Load the full hierarchy of organizational units.
	 * 
	 * @param Ou_model $parent The starting point, or null to start at the root.
	 */
	function getHierarchy(Ou_model $parent = null) {
		if($parent == null) {
			$parent = Ou_model::get_by_ou_name("root");
			if(!$parent) {
				/* Special case for non-existent root */
				throw new Exception("Root OU does not exist");
			}
		}

		$parent -> populate_list_Ou();
		foreach($parent -> list_Ou as $key => $ou) {
			$parent -> list_Ou[$key] = self::getHierarchy($ou);
		}
		
		return $parent;
	}

	/**
	 * Create a new organizational unit
	 * 
	 * @param string $ou_name The name of the new organizational unit
	 * @param int $ou_parent_id The parent OU (to create it in)
	 * @throws Exception
	 * @return Ou_model The newly created organizational unit
	 */
	function create($ou_name, $ou_parent_id) {
		$ou_name = Auth::normaliseName($ou_name);
		$ou_parent_id = (int)$ou_parent_id;
		
		/* Check name */
		if($ou = Ou_model::get_by_ou_name($ou_name)) {
			throw new Exception("An organizational unit with that name already exists");
		}

		if($ug = UserGroup_model::get_by_group_cn($ou_name)) {
			throw new Exception("There is a group which goes by this name. Please use a different name.");
		}
		
		/* Check parent is real */
		if(!$parent = Ou_model::get($ou_parent_id)) {
			throw new Exception("The parent organizational unit could not be found (did somebody delete it?)");
		}

		/* Attempt to insert */
		$ou =  new Ou_model();
		$ou -> ou_name = $ou_name;
		$ou -> ou_parent_id = $ou_parent_id;
		if(!$ou -> ou_id = $ou -> insert()) {
			throw new Exception("There was an error adding the unit to the database. Please try again.");
		}

		/* ActionQueue */
		ActionQueue_api::submitToEverything('ouCreate', $ou -> ou_name, $parent -> ou_name);
		return $ou;
	}
	
	/**
	 * Delete an organizational unit.
	 * 
	 * @param integer $ou_id The ID of the unit to delete.
	 * @throws Exception
	 */
	function delete($ou_id) {
		$ou = self::get($ou_id);
		
		if($ou -> ou_name == "root") {
			throw new Exception("Cannot delete the root of the organization.");
		}
		
		foreach($ou -> list_Ou as $child) {
			self::move($child -> ou_id, $ou -> ou_parent_id);
		}
		
		foreach($ou -> list_AccountOwner as $owner) {
			AccountOwner_api::move($owner -> owner_id, $ou -> ou_parent_id);
		}
		
		foreach($ou -> list_UserGroup as $group) {
			UserGroup_api::move($group -> group_id, $ou -> ou_parent_id);
		}
		
		/* ActionQueue */
		ActionQueue_api::submitToEverything('ouDelete', $ou -> ou_name);
		
		$ou -> delete();
	}
	
	/**
	 * Re-base an organizational unit, to go under a different parent
	 * 
	 * @param int $ou_id The unit to move.
	 * @param int $ou_parent_id The new parent unit.
	 */
	function move($ou_id, $ou_parent_id) {
		$ou = self::get($ou_id);
		$parent = self::get($ou_parent_id);
		
		// TODO search hierarchy to prevent infinite loops
		$ou -> ou_parent_id = $parent -> ou_id;
		$ou -> update();
		
		/* ActionQueue */
		ActionQueue_api::submitToEverything('ouMove', $ou -> ou_name, $parent -> ou_name);
	}
	
	/**
	 * Change the name of an organizational unit.
	 * 
	 * @param int $ou_id
	 * @param string $ou_name The new name of the unit (this will be filtered for sanity)
	 * @throws Exception
	 */
	function rename($ou_id, $ou_name) {
		$ou = self::get($ou_id);
		$ou_name = Auth::normaliseName($ou_name);
		
		if($ou = $ou -> get_by_ou_name($ou_name)) {
			throw new Exception("An organizational unit with that name already exists");
		}
		
		if(!$ou = Ou_model::get((int)$ou_id)) {
			throw new Exception("No such organizational unit");
		}
		
		if($ug = UserGroup_model::get_by_group_cn($ou_name)) {
			throw new Exception("There is a group which goes by this name. Please use a different name.");
		}
		
		$oldname = $ou -> ou_name;
		$ou -> ou_name = $ou_name;
		$ou -> update();
		
		/* ActionQueue */
		ActionQueue_api::submitToEverything('ouMove', $oldname, $ou -> ou_name);
	}
	
	/**
	 * Get an organizational unit by its numeric ID.
	 * 
	 * @param int $ou_id
	 * @throws Exception If it cannot be found
	 * @return Ou_model the Organizational unit.
	 */
	function get($ou_id) {
		if(!$ou = Ou_model::get((int)$ou_id)) {
			throw new Exception("No such organizational unit");
		}
		
		$ou -> populate_list_Ou();
		$ou -> populate_list_AccountOwner();
		$ou -> populate_list_UserGroup();
		
		return $ou;
	}
}

?>