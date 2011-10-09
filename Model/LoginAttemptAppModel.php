<?php

class LoginAttemptAppModel extends AppModel {
	/**
	 * Default Constructor
	 *
	 * @return void
	 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->__setupValidation();
	}
	
	/**
	 * Load Validation
	 * 
	 * This method is used to allow internationalization of validation messages,
	 * which is not permitted if defined as a class variable.
	 * 
	 * @return void 
	 */
	public function __setupValidation() {
	}
}

?>