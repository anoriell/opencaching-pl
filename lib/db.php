<?php
/**
 * Class for safe database operations
 * 
 * This class use newest php database library PDO, recomended for use for database operations.
 * using pdo is not so easy as classic mysql_* functions, but provide safety and easy to use
 * result. (array) 
 * 
 * most important methode in this class is paramQuery(). instructions and example of use included.
 * 
 * @author Andrzej Łza Woźniak
 *
 */
class dataBase
{
	/**
	 * set this value to true to print all variables to screen.
	 * set to false to hide (switch off) all printed debug.
	 */
	private $debug = false;
	
	/**
	 * database link setup
	 * @var string
	 */
	private $server	= null;
	private $name = null;
	private $username = null;
	private $password = null;
	
	function __construct() {
	 include 'lib/settings.inc.php';
	 $this->server   = $opt['db']['server'];
	 $this->name     = $opt['db']['name'];
	 $this->username = $opt['db']['username'];
	 $this->password = $opt['db']['password'];
	}

	/**
	 * simple querry 
	 * Use only with static queries, Queries should contain no variables.
	 * For queries with variables use paramQery methode
	 * 
	 * @param string $query
	 * @return array
	 */
	public function simpleQuery($query) {
		$dbh = new PDO("mysql:host=".$this->server.";dbname=".$this->name,$this->username,$this->password);
		$dbh -> query ('SET NAMES utf8');
		$dbh -> query ('SET CHARACTER_SET utf8_unicode_ci');

		$STH = $dbh -> prepare($query);
		$STH -> execute();

		return $STH -> fetch();
	}

	/**
	 * @param $query - string, with params representation instead variables.
	 * @param $params - array with variables.
	 * 
	 * [keyname][value]
	 * [keyname][data_type]
	 * 
	 * example:
	 * ----------------------------------------------------------------------------------
	 * $query: 'SELECT * FROM tabele WHERE field1=:variable1 AND field2:variable2'
	 * $params[variable1][value] = 1;
	 * $params[variable1][data_type] = 'integer';
	 * $params[variable2][value] = 'cat is very lovelly animal';
	 * $params[variable2][data_type] = 'string';
	 * ----------------------------------------------------------------------------------
	 * data type can be:
	 * 
	 * - 'boolean'		Represents a boolean data type.
	 * - 'null' 		Represents the SQL NULL data type.
	 * - 'integer' 		Represents the SQL INTEGER data type.
	 * - 'string' 		Represents the SQL CHAR, VARCHAR, or other string data type.
	 * - 'large' 		Represents the SQL large object data type.
	 * - 'recordset' 	Represents a recordset type. Not currently supported by any drivers.
	 * 
	 * @return array or false
	 *  - return array structure:
	 *  Array
	 * (
     *   [row_count] => 1
     *   [result] => Array
     *     (
     *       [secid] => 12
     *       [0] => 12
     *     )
	 * )
	 */
	public function paramQuery($query, $params) {
		if (!is_array($params)) return false;

		$dbh = new PDO("mysql:host=".$this->server.";dbname=".$this->name,$this->username,$this->password);
		$dbh -> query ('SET NAMES utf8');
		$dbh -> query ('SET CHARACTER_SET utf8_unicode_ci');

		$stmt = $dbh->prepare($query);
		
		foreach ($params as $key => $val) {
			switch ($val['data_type']) {
				case 'integer':
					$stmt->bindParam($key, $val['value'], PDO::PARAM_INT);
					break;
				case 'boolean':
						$stmt->bindParam($key, $val['value'], PDO::PARAM_BOOL);
						break;
				case 'string':
					$stmt->bindParam($key, $val['value'], PDO::PARAM_STR);
					break;
				case 'null':
					$stmt->bindParam($key, $val['value'], PDO::PARAM_NULL);
					break;
					
			    case 'large':
					$stmt->bindParam($key, $val['value'], PDO::PARAM_LOB);
					break;
				case 'recordset':
					$stmt->bindParam($key, $val['value'], PDO::PARAM_STMT);
					break;
				default:
					return false;
			} 
		}
		
		$stmt->execute();

		$result['row_count'] = $stmt->rowCount();
		$result['result'] = $stmt -> fetch();

		
		if ($this->debug) {
			print 'db.php, # ' . __line__ .', mysql query on input: ' . $query .'<br />';
			self::debugOC('db.php, # ' . __line__ .', input parametres for query', $params );
			self::debugOC('db.php, # ' . __line__ .', database output', $result );
		}
		
		return $result;
	}
	
	/**
	 * this methode can be used for display any array from anywhere
	 * 
	 * @param string $position - put here what you want. just title(name) of array
	 * @param array $array - array to display
	 * 
	 * @example dataBase::debugOC('some.php, # ' . __line__ .', my variable', $array_variable );
	 */
	public static function debugOC($position, $array) {
		print "<pre> --- $position --<br>";
		print_r ($array);
		print '----------------------<br /><br /></pre>';
		
	}

}




/* other exapmples
$pdo=new PDO('...');
$stmt=$pdo->prepare('SELECT * FROM tabela WHERE id=:imie');
$stmt->bindParam(':imie',$_POST['imie'],PDO::PARAM_STR);
$stmt->execute();
...

$q = $db->prepare('INSERT INTO tabela (p1,p2) VALUES (:p1, :p2)');
$q -> execute(array('p1' => 'wartosc', 'p2' => 'inna'));


$db=new PDO("mysql:cośtam","user","hasło");

$stmt=$db->prepare("select pole1, pole2 from tabela where pole1=:wartosc1 and pole2=:wartosc2");
$stmt->bindValue(":wartosc1",$_POST["wartosc1"]);
$stmt->bindValue(":wartosc2",$_POST["wartos21"]);

$stmt->execute();
*/