<?php namespace BackupManager\Databases;

/**
 * Class MysqlDatabase
 * @package BackupManager\Databases
 */
class MysqlDatabase implements Database {

    /** @var array */
    private $config;

    /**
     * @param $type
     * @return bool
     */
    public function handles($type) {
        return strtolower($type) == 'mysql';
    }

    /**
     * @param array $config
     * @return null
     */
    public function setConfig(array $config) {
        $this->config = $config;
    }

    /**
     * @param $outputPath
     * @return string
     */
    public function getDumpCommandLine($outputPath) {
    	$extras = [];
    	if (array_key_exists('singleTransaction', $this->config) && $this->config['singleTransaction'] === true) {
    		$extras[] = '--single-transaction';
    	}
        if (array_key_exists('ignoreTables', $this->config)) {
            $extras[] = $this->getIgnoreTableParameter();
        }
        if (array_key_exists('ssl', $this->config) && $this->config['ssl'] === true) {
    		$extras[] = '--ssl';
    	}
    	if (array_key_exists('verbose', $this->config) && $this->config['verbose'] === true) {
    		$extras[] = '--verbose';
    	}

    	$command = 'mysqldump --routines '.implode(' ', $extras).' --host=%s --port=%s --user=%s --password=%s %s > %s';

    	return sprintf($command,
            escapeshellarg($this->config['host']),
            escapeshellarg($this->config['port']),
            escapeshellarg($this->config['user']),
            escapeshellarg($this->config['pass']),
            escapeshellarg($this->config['database']),
            escapeshellarg($outputPath)
        );
    }

    /**
     * @param $inputPath
     * @return string
     */
    public function getRestoreCommandLine($inputPath) {
        $extras = [];
        if (array_key_exists('ssl', $this->config) && $this->config['ssl'] === true) {
    		$extras[] = '--ssl';
    	}
        return sprintf('mysql --host=%s --port=%s --user=%s --password=%s '.implode(' ', $extras).' %s -e "source %s"',
            escapeshellarg($this->config['host']),
            escapeshellarg($this->config['port']),
            escapeshellarg($this->config['user']),
            escapeshellarg($this->config['pass']),
            escapeshellarg($this->config['database']),
            $inputPath
        );
    }

    /**
     * @return string
     */
    public function getIgnoreTableParameter() {

        if (!is_array($this->config['ignoreTables']) || count($this->config['ignoreTables']) === 0) {
            return '';
        }

        $db = $this->config['database'];
        $ignoreTables = array_map(function($table) use ($db) {
            return $db.'.'.$table;
        }, $this->config['ignoreTables']);

        $commands=[];
        foreach($ignoreTables AS $ignoreTable) {
            $commands[]=sprintf('--ignore-table=%s',
                escapeshellarg($ignoreTable)
            );
        }

        return implode(' ',$commands);
    }
}
