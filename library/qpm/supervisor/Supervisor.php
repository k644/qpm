<?php
namespace qpm\supervisor;
require_once 'qpm/supervisor/Config.php';
require_once 'qpm/log/Logger.php';
use qpm\log\Logger;

class Supervisor {
	/**
	 *@ return qpm\supervisor\Supervisor
	 */
	public static function taskFactoryMode($conf) {
		require_once __DIR__.'/TaskFactoryKeeper.php';
		$config = new Config($conf);
		return new self(new TaskFactoryKeeper($config));
	}
	/**
	 * @return qpm\supervisor\Supervisor
	 */
	public static function oneForOne($config) {
		$configs = [new Config($config)];
		return self::_oneForOne($configs);
	}
	/**
	 * @return qpm\supervisor\Supervisor
	 */
	public static function multiGroupOneForOne($configs) {
		if (!is_array($configs) and !($configs instanceof \Iterator)) {
			throw new \InvalidArgumentException('exptects an array or Iterator'); 
		}
		if (!count($configs)) {
			throw new \InvalidArgumentException('at least 1 item');
		}
		$cs = array();
		foreach($configs as $c) {
			$cs[] = new Config($c);
		}
		return self::_oneForOne($cs);
	}
	/**
	 * @return qpm\supervisor\Supervisor
	 */
	private static function _oneForOne($configs) {
		require_once __DIR__.'/OneForOneKeeper.php';
		return new self(new OneForOneKeeper($configs));
	}
	
	private $_keeper;
	public function __construct($keeper) {
		$this->_keeper = $keeper;
	}
	
	public function getKeeper() {
		return $this->_keeper;
	}
	
	public function start() {
		Logger::debug(__CLASS__.'::'.__METHOD__.' before keeper startall');
		$this->_keeper->startAll();
		Logger::debug(__CLASS__.'::'.__METHOD__.' before before keeper keep');
		$this->_keeper->keep();
		Logger::debug(__CLASS__.'::'.__METHOD__.' after keeper keep');
	}
	
	public function stop() {
		Logger::debug(__CLASS__.'::'.__METHOD__.' before keeper stop');
		$this->_keeper->stop();
		Logger::debug(__CLASS__.'::'.__METHOD__.' after keeper stop');
	}
	
	public function registerSignalHandler() {
		pcntl_signal(SIGTERM, array($this, 'stop'));
	}
}
