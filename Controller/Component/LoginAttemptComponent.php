<?php
App::uses('LoginAttempt', 'LoginAttempt.Model');

class LoginAttemptComponent extends Component {
	/**
	 * This method checks to see if the user should be allowed access
	 * 
	 * @param string $action
	 * @param integer $limit
	 * @return boolean
	 */
	public function allowAccess($action, $limit){
		$attempt = $this->LoginAttempt->find('first',array(
			'conditions' => array(
				'IP' => $this->_getIP(),
				'action' => $action,
			),
			'fields' => array(
				'LoginAttempt.count',
				'LoginAttempt.expire'
			)
		));
		
		// Remove the Stale Lock and allow attempt
		if(strtotime($attempt['LoginAttempt']['expire']) < time()) {
			$this->reset($action);
		}
		
		if($attempt['LoginAttempt']['count'] > $limit) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * This method records an authentication failure and saves it in the DB
	 * The expire parameter should take the format of a DateInterval object
	 * http://us.php.net/manual/en/dateinterval.construct.php
	 * 
	 * @param string $action
	 * @param string $expire
	 */
	public function fail($action, $expire="T15M"){
		$ip = $this->_getIP();
		
		$attempt = $this->LoginAttempt->find('first', array(
			'conditions' => array(
				'IP' => $ip,
				'action' => $action
			),
			'fields' => array(
				'LoginAttempt.id',
				'LoginAttempt.count'
			)
		));
		
		// This is the users' first failed attempt
		if(!$attempt) {
			$attempt['LoginAttempt']['IP'] = $ip;
			$attempt['LoginAttempt']['action'] = $action;
			$date = new DateTime();
			$date->add(new DateInterval('P'.$expire));
			$attempt['LoginAttempt']['expire'] = $date->format('Y-m-d H:i:s');
			$attempt['LoginAttempt']['count'] = 1;
			
			$this->LoginAttempt->create();
			$this->LoginAttempt->save($attempt);
		} else {
			$attempt['LoginAttempt']['count'] += 1;
			$this->LoginAttempt->save($attempt); 
		}
	}
	
	/**
	 * This method is called before the controller's beforeFilter method
	 * More specifically, it instantiates the LoginAttempts model
	 * 
	 * @return void
	 */
	public function initialize(&$Controller) {
		$this->LoginAttempt = ClassRegistry::init('LoginAttempt.LoginAttempt');
	}
	
	/**
	 * This method resets the lock status for a user
	 * 
	 * @param string $action
	 * @return void
	 */
	public function reset($action) {		
		$attempt = $this->LoginAttempt->find('first',array(
			'conditions' => array(
				'IP' => $this->_getIP(),
				'action' => $action
			),
			'fields' => array(
				'LoginAttempt.id'
			)
		));
		
		$this->LoginAttempt->delete($attempt['LoginAttempt']['id']);
	}
	
	/**
	 * This is a method for grabbing the clients IP
	 * 
	 * @return string 
	 */
	private function _getIP() {
		if(empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = gethostbyname($_SERVER['REMOTE_ADDR']);
		} else {
			$splits = explode(', ',$_SERVER['HTTP_X_FORWARDED_FOR']);
			$ip = $splits[0];
		}
		
		return $ip;
	}
}
?>