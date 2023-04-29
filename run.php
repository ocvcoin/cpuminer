#!/usr/bin/env php
<?php

if (posix_getuid() !== 0) {
    echo "
        Please run as root
    ";
    exit;
}









if (empty($argv[1])) {
	
$OCVADDR_FILE = $_SERVER['HOME'] . '/.OCVADDR';

if (file_exists($OCVADDR_FILE)) {
    $OCVADDR = trim(file_get_contents($OCVADDR_FILE));
    echo "
        1 - Use this address: $OCVADDR
        2 - Enter new address
    ";
    $CONTINUE = trim(readline());
    if ($CONTINUE === "2") {
        echo "
		
            Please paste/enter new Ocvcoin address:

        ";
        $OCVADDR = trim(readline());
        file_put_contents($OCVADDR_FILE, $OCVADDR);
    }elseif ($CONTINUE !== "1"){
		
		echo "
		
		You have not selected a valid option.
		We assume you chose option 1 ...
		
		";
		
		
	}
} else {
    echo "
        Please paste/enter your Ocvcoin address:

    ";
    $OCVADDR = trim(readline());
    file_put_contents($OCVADDR_FILE, $OCVADDR);
}		
	
	
} else {
    $OCVADDR = $argv[1];
}

if (!isBech32($OCVADDR)) {
    echo "
        Invalid Ocvcoin address! Address must be bech32! (start with ocv1...)
    ";
	unlink($OCVADDR_FILE);
    exit;
}
















if (empty($argv[2])) {
    
    echo "

Select Mining Option:

1 - Solo mining with your own Ocvcoin Core! Recommended in China

2 - Mining with Pool

";
	
	$miningoption = trim(readline());
	
	
} else {
    $miningoption = $argv[2];
}

if ($miningoption !== "1" && $miningoption !== "2") {


		echo "
		
		You have not selected a valid option.
		We assume you chose option 1 ...
		
		";
		$miningoption = "1";

}



if ($miningoption === "2") {
	
	$stratum_servers = array(

"fi.mining4people.com"=>        array("port"=>"3376","solo_port"=>"3379","title"=>"Finland"),
"au.mining4people.com"=>        array("port"=>"3376","solo_port"=>"3379","title"=>"Australia"),
"in.mining4people.com"=>        array("port"=>"3376","solo_port"=>"3379","title"=>"India"),
"us.mining4people.com"=>        array("port"=>"3376","solo_port"=>"3379","title"=>"United States"),
"de.mining4people.com"=>        array("port"=>"3376","solo_port"=>"3379","title"=>"Germany"),
"br.mining4people.com"=>        array("port"=>"3376","solo_port"=>"3379","title"=>"Brazil"),
"eu-stratum.phalanxmine.com"=>  array("port"=>"5120","solo_port"=>"5120","title"=>"Sweden"),
"asia-stratum.phalanxmine.com"=>array("port"=>"5120","solo_port"=>"5120","title"=>"Singapore"),
"us-stratum.phalanxmine.com"=>  array("port"=>"5120","solo_port"=>"5120","title"=>"United States"),
"aus-stratum.phalanxmine.com"=> array("port"=>"5120","solo_port"=>"5120","title"=>"Australia"),


);
	
	
    if (empty($argv[3])) {
		
		
		
		
	$i=3;
	$index2server_arr = array();
        echo "
		
Select Mining Pool:

1 - AUTO (automatically finds the nearest pool)
2 - AUTO (automatically finds the nearest pool) (SOLO POOL)
";	
	foreach($stratum_servers as $stratum_server => $data){
		
		echo "$i - $stratum_server ".$data["title"]."\n";
		$index2server_arr[$i] = array($stratum_server,"pplns");
		$i++;
	}	
	foreach($stratum_servers as $stratum_server => $data){
		
		echo "$i - $stratum_server ".$data["title"]." (SOLO POOL)\n";
		$index2server_arr[$i] = array($stratum_server,"solo");
		$i++;
	}		
		
		
        
        $miningserver = trim(readline());
		
		
    } else {
        $miningserver = $argv[3];
    }

    if ($miningserver !== "1" && $miningserver !== "2" && !isset($index2server_arr[$miningserver])) {


		echo "
		
		You have not selected a valid option.
		We assume you chose option 1 ...
		
		";
		$miningserver = "1";


    }
	
	
	
	
if (empty($argv[4])) {
    
    echo "

Select Connection Option:

1 - Use SSL

2 - No Use SSL

";
	
	$ssloption = trim(readline());
	
	
} else {
    $ssloption = $argv[4];
}

if ($ssloption !== "1" && $ssloption !== "2") {


		echo "
		
		You have not selected a valid option.
		We assume you chose option 1 ...
		
		";
		$ssloption = "1";


}	
	
	
	
	
	
	if($miningserver === "1" or $miningserver === "2"){
		
		
		
		
		$pinged_servers = array();
		foreach($stratum_servers as $stratum_server => $data){
			
			echo "
			
			checking $stratum_server
			
			";
			
			$stratum_server_a_records = get_dns_records($stratum_server,"A");
			
			$lowest_ping = PHP_INT_MAX;
			$lowest_ping_ip = "";
			
			foreach($stratum_server_a_records as $record){
				
				
				if (filter_var(@$record["data"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
					if($miningserver === "1")
						$ping_result = pingDomain($record["data"],(($ssloption === "1") ? "2" : "").$data["port"]);
					else
						$ping_result = pingDomain($record["data"],(($ssloption === "1") ? "2" : "").$data["solo_port"]);
					
					
					if($ping_result > 0 && $ping_result < $lowest_ping){
						
						$lowest_ping = $ping_result;
						$lowest_ping_ip = $record["data"];						
						
					}
					
        
				}
				
				
			}
			if($lowest_ping_ip !== "")				
				$pinged_servers[$lowest_ping] = array($stratum_server,$lowest_ping_ip);
				
				
			
			
			
			
		}
		
		if(empty($pinged_servers))
			exit("
		no found any online pool!
		");
		
		echo "
		
		ping results:
		
		";
		print_r($pinged_servers);
		
		
		
		ksort($pinged_servers,SORT_NUMERIC );
		
		$selected_server = reset($pinged_servers);
		

		
		if($miningserver === "1")
			$cpuminer_args = " -a ocv2 -o stratum+tcp".(($ssloption === "1") ? "s" : "")."://".$selected_server[1].":".(($ssloption === "1") ? "2" : "").$stratum_servers[$selected_server[0]]["port"]." -u $OCVADDR -p x";
		else
			$cpuminer_args = " -a ocv2 -o stratum+tcp".(($ssloption === "1") ? "s" : "")."://".$selected_server[1].":".(($ssloption === "1") ? "2" : "").$stratum_servers[$selected_server[0]]["solo_port"]." -u $OCVADDR -p x,m=solo";
		
	}else {
		
		$requested_server = $index2server_arr[$miningserver];//hostname ,pplns or solo
		
		$stratum_server_a_records = get_dns_records($requested_server[0],"A");
			
			
			$is_found = false;
			foreach($stratum_server_a_records as $record)				
				if (filter_var(@$record["data"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)){
					$is_found = true;
					$selected_server = array($requested_server[0],$record["data"]);
					break;
				}
		if(!$is_found)
			exit ("
		
		hostname ".$requested_server[0]." resolve failed. Please select another option.
		
		");
		
		if($requested_server[1] === "pplns")
			$cpuminer_args = " -a ocv2 -o stratum+tcp".(($ssloption === "1") ? "s" : "")."://".$selected_server[1].":".(($ssloption === "1") ? "2" : "").$stratum_servers[$selected_server[0]]["port"]." -u $OCVADDR -p x";
		else
			$cpuminer_args = " -a ocv2 -o stratum+tcp".(($ssloption === "1") ? "s" : "")."://".$selected_server[1].":".(($ssloption === "1") ? "2" : "").$stratum_servers[$selected_server[0]]["solo_port"]." -u $OCVADDR -p x,m=solo";		
		
		
		
	}
	
		echo "
		
		Selected server:
		
		";
		print_r($selected_server);
		echo "\n";
		print_r($stratum_servers[$selected_server[0]]);
		echo "\n";	
	

    
}




if ($miningoption === "1") {
    if (!file_exists('/usr/local/bin/ocvcoind')) {
		
		
		
$machine = php_uname('m');
if ($machine !== 'x86_64') {
	
  exit("
  
  Ocvcoin Core can only be installed on x86-based machines with 64-bit processors.
  Please choose pool mining.
  
  "); 
  
  
} 		
		
		
		
		
		
        echo "
            Installing Ocvcoin Core...
        ";
        passthru('wget -qO - https://raw.githubusercontent.com/ocvcoin/ocvcoin/master/UBUNTU_AUTO_BUILD_AND_INSTALL.sh | sudo bash');
    }

    if (!file_exists('/usr/local/bin/ocvcoind'))
        exit ("
		Ocvcoin Core installation failed!
		");
    


$parse_ocvcoin_conf_file = parse_ini_file("/etc/ocvcoin/ocvcoin.conf",true);

$is_found=false;
$i=0;
foreach(explode(",","rpcuser,rpcpassword,rpcallowip,rpcbind,rpcport,server") as $settname)
	if(@strlen($parse_ocvcoin_conf_file["main"][$settname]))
		$i++;
	
	

if($i !== 6){




$ocvcoinconffilenewcontent = "[main]

rpcuser=ocvcoinrpc
rpcpassword=".base64url_encode(random_bytes(32))."
rpcallowip=0.0.0.0/0
rpcbind=0.0.0.0
rpcport=8332
server=1

";

file_put_contents('/etc/ocvcoin/ocvcoin.conf', $ocvcoinconffilenewcontent);

$parse_ocvcoin_conf_file = parse_ini_file("/etc/ocvcoin/ocvcoin.conf",true);

if(@count($parse_ocvcoin_conf_file["main"]) !== 6)
	exit("

UNKNOWN ERROR ON LINE ".__LINE__."

");

PASSTHRU('systemctl restart ocvcoind.service');

}else 
	PASSTHRU('systemctl start ocvcoind.service');






$shell_ret = shell_exec('host -t AAAA dnsseed.ocvcoin.com ns1.desec.io');

$ips = preg_split("#\s+#", $shell_ret);

foreach ($ips as $ip) {
	if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
		{
			
			echo "adding found node: $ip
			";
			while(rpc_query("addnode",array($ip,"onetry")) !== NULL){
				
				echo "
				addnode error! sleeping 10 sec!
				";
				sleep(10);
			
			}
			
			
		}
}



$shell_ret = shell_exec('host -t A dnsseed.ocvcoin.com ns2.desec.org');

$ips = preg_split("#\s+#", $shell_ret);

foreach ($ips as $ip) {
	if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
		{
			
			echo "adding found node: $ip
			";
			while(rpc_query("addnode",array($ip,"onetry")) !== NULL){
				
				echo "
				addnode error! sleeping 10 sec!
				";
				sleep(10);
			
			}
			
			
		}
}


$cpuminer_args = "--no-getwork  --userpass=".$parse_ocvcoin_conf_file["main"]["rpcuser"].":".$parse_ocvcoin_conf_file["main"]["rpcpassword"]." --url=http://127.0.0.1:8332/ --algo=ocv2  --coinbase-addr=$OCVADDR --coinbase-sig=".base64url_encode(random_bytes(16));

}



$miner_bin = $_SERVER['HOME'] . '/ocvcoin_cpuminer/cpuminer/minerd';

if(!file_exists($miner_bin)){
	
	
	echo "
            Installing cpuminer...
    ";
	PASSTHRU("wget -O build.sh https://raw.githubusercontent.com/ocvcoin/cpuminer/master/build.sh && sudo bash build.sh 1");
	
	
}

if(!file_exists($miner_bin)){
	
	
	exit ("
            cpuminer installation failed!
    ");
	
	
	
}




if ($miningoption === "1") {
	
	echo "
	
	We check that the Ocvcoin Core synchronization is complete and working well...
	
	";
	
	while(true){
		
		
		$rpcret = rpc_query("getblockchaininfo",array());
		
		if(!$rpcret or @$rpcret["initialblockdownload"])			
		 {
			
			echo "			
			Verification Progress: ".(@$rpcret["verificationprogress"]*100)."%
			We'll wait 10 seconds and try again. 			
			";
			sleep(10);
		
		}
		else break;
		
	}
	
	
	
	
	
	
	
	
	
	
}



echo "


MINING STARTING...!

START COMMAND:
$miner_bin $cpuminer_args


";



PASSTHRU ($miner_bin ." " .$cpuminer_args);













function get_dns_records($hostname,$type) {
    // DNS over HTTPS endpoint URL
    $url = 'https://mozilla.cloudflare-dns.com/dns-query?name=' . urlencode($hostname) . '&type='.$type;

    // HTTP headers for the request
    $headers = [
        'Accept: application/dns-json'
    ];

    // Set the stream context options for the request
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/dns-json\r\n",
            'follow_location' => true,
        ],
    ];
    $context = stream_context_create($options);

    // Make the request using file_get_contents
    $result = file_get_contents($url, false, $context);

    // Check for errors
    if ($result === false) {
        return false;
    }

    // Parse the JSON response and return the results
    $response = json_decode($result, true);
    return isset($response['Answer']) ? $response['Answer'] : [];
}




function pingDomain($domain,$port){
    $starttime = microtime(true);
    $file      = fsockopen ($domain, $port, $errno, $errstr, 10);
    $stoptime  = microtime(true);
    $status    = 0;

    if (!$file) $status = -1;  // Site is down
    else {
        fclose($file);
        $status = ($stoptime - $starttime)*10000;
        $status = floor($status);
    }
	echo "
	
	ping $domain:$port = $status
	
	";
    return $status;
}







class Bech32
{
    public const BECH32 = 'bech32';
    public const BECH32M = 'bech32m';

    public const GENERATOR = [0x3b6a57b2, 0x26508e6d, 0x1ea119fa, 0x3d4233dd, 0x2a1462b3];
    public const CHARSET = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';
    public const CHARKEY_KEY = [
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
        15, -1, 10, 17, 21, 20, 26, 30,  7,  5, -1, -1, -1, -1, -1, -1,
        -1, 29, -1, 24, 13, 25,  9,  8, 23, -1, 18, 22, 31, 27, 19, -1,
        1,  0,  3, 16, 11, 28, 12, 14,  6,  4,  2, -1, -1, -1, -1, -1,
        -1, 29, -1, 24, 13, 25,  9,  8, 23, -1, 18, 22, 31, 27, 19, -1,
        1,  0,  3, 16, 11, 28, 12, 14,  6,  4,  2, -1, -1, -1, -1, -1
    ];

    /**
     * @param string $hrp Human-readable part
     * @param int $version Segwit script version
     * @param string $program Segwit witness program
     * @param string $encoding
     * @return string The encoded address
     * @throws Bech32Exception
     */
    public function encodeSegwit(string $hrp, int $version, string $program, string $encoding)
    {
        $this->validateWitnessProgram($version, $program);

        $programChars = array_values(unpack('C*', $program));
        $programBits = $this->convertBits($programChars, count($programChars), 8, 5);
        $encodeData = array_merge([$version], $programBits);

        return $this->encode($hrp, $encodeData, $encoding);
    }

    /**
     * @param string $hrp Human-readable part
     * @param string $bech32 Bech32 string to be decoded
     * @param string $encoding
     * @return array [$version, $program]
     * @throws Bech32Exception
     */
    public function decodeSegwit(string $hrp, string $bech32, string $encoding)
    {
        list($hrpGot, $data) = $this->decode($bech32, $encoding);

        if ($hrpGot !== $hrp) {
            throw new Exception('Invalid prefix for address');
        }

        $dataLen = count($data);

        if ($dataLen === 0 || $dataLen > 65) {
            throw new Exception("Invalid length for segwit address");
        }

        $decoded = $this->convertBits(array_slice($data, 1), count($data) - 1, 5, 8, false);
        $program = pack("C*", ...$decoded);

        $this->validateWitnessProgram($data[0], $program);

        return [$data[0], $program];
    }

    /**
     * @param string $hrp
     * @param array $combinedDataChars
     * @param string $encoding
     * @return string
     */
    private function encode(string $hrp, array $combinedDataChars, string $encoding)
    {
        $checksum = $this->createChecksum($hrp, $combinedDataChars, $encoding);
        $characters = array_merge($combinedDataChars, $checksum);

        $encoded = [];
        for ($i = 0, $n = count($characters); $i < $n; $i++) {
            $encoded[$i] = self::CHARSET[$characters[$i]];
        }

        return "{$hrp}1" . implode('', $encoded);
    }

    /**
     * @param string $hrp
     * @param int[] $convertedDataChars
     * @param string $encoding
     * @return int[]
     */
    private function createChecksum(string $hrp, array $convertedDataChars, string $encoding)
    {
        $values = array_merge($this->hrpExpand($hrp, strlen($hrp)), $convertedDataChars);
        $polyMod = $this->polyMod(array_merge($values, [0, 0, 0, 0, 0, 0]), count($values) + 6) ^ $this->getEncoding($encoding);
        $results = [];
        for ($i = 0; $i < 6; $i++) {
            $results[$i] = ($polyMod >> 5 * (5 - $i)) & 31;
        }

        return $results;
    }

    /**
     * Validates a bech32 string and returns [$hrp, $dataChars] if
     * the conversion was successful. An exception is thrown on invalid
     * data.
     *
     * @param string $sBech The bech32 encoded string
     * @param string $encoding
     * @return array Returns [$hrp, $dataChars]
     * @throws Bech32Exception
     */
    private function decode(string $sBech, string $encoding)
    {
        $length = strlen($sBech);

        if ($length > 90) {
            throw new Exception('Bech32 string cannot exceed 90 characters in length');
        }

        return $this->decodeRaw($sBech, $encoding);
    }

    /**
     * @throws Bech32Exception
     * @param string $sBech The bech32 encoded string
     * @param string $encoding
     * @return array Returns [$hrp, $dataChars]
     */
    private function decodeRaw(string $sBech, string $encoding)
    {
        $length = strlen($sBech);

        if ($length < 8) {
            throw new Exception("Bech32 string is too short");
        }

        $chars = array_values(unpack('C*', $sBech));

        $haveUpper = false;
        $haveLower = false;
        $positionOne = -1;

        for ($i = 0; $i < $length; $i++) {
            $x = $chars[$i];

            if ($x < 33 || $x > 126) {
                throw new Exception('Out of range character in bech32 string');
            }

            if ($x >= 0x61 && $x <= 0x7a) {
                $haveLower = true;
            }

            if ($x >= 0x41 && $x <= 0x5a) {
                $haveUpper = true;
                $x = $chars[$i] = $x + 0x20;
            }

            // find location of last '1' character
            if ($x === 0x31) {
                $positionOne = $i;
            }
        }

        if ($haveUpper && $haveLower) {
            throw new Exception('Data contains mixture of higher/lower case characters');
        }

        if ($positionOne === -1) {
            throw new Exception("Missing separator character");
        }

        if ($positionOne < 1) {
            throw new Exception("Empty HRP");
        }

        if (($positionOne + 7) > $length) {
            throw new Exception('Too short checksum');
        }

        $hrp = pack("C*", ...array_slice($chars, 0, $positionOne));

        $data = [];

        for ($i = $positionOne + 1; $i < $length; $i++) {
            $data[] = ($chars[$i] & 0x80) ? -1 : self::CHARKEY_KEY[$chars[$i]];
        }

        if (!$this->verifyChecksum($hrp, $data, $encoding)) {
            throw new Exception('Invalid bech32 checksum');
        }

        return [$hrp, array_slice($data, 0, -6)];
    }

    /**
     * Verifies the checksum given $hrp and $convertedDataChars.
     *
     * @param string $hrp
     * @param int[] $convertedDataChars
     * @param string $encoding
     * @return bool
     */
    private function verifyChecksum(string $hrp, array $convertedDataChars, string $encoding)
    {
        $expandHrp = $this->hrpExpand($hrp, strlen($hrp));
        $r = array_merge($expandHrp, $convertedDataChars);
        $poly = $this->polyMod($r, count($r));

        return $poly === $this->getEncoding($encoding);
    }

    /**
     * Expands the human-readable part into a character array for checksumming.
     *
     * @param string $hrp
     * @param int $hrpLen
     * @return int[]
     */
    private function hrpExpand(string $hrp, int $hrpLen)
    {
        $expand1 = [];
        $expand2 = [];

        for ($i = 0; $i < $hrpLen; $i++) {
            $o = ord($hrp[$i]);
            $expand1[] = $o >> 5;
            $expand2[] = $o & 31;
        }

        return array_merge($expand1, [0], $expand2);
    }

    /**
     * @param int[] $values
     * @param int $numValues
     * @return int
     */
    private function polyMod(array $values, int $numValues)
    {
        $chk = 1;
        for ($i = 0; $i < $numValues; $i++) {
            $top = $chk >> 25;
            $chk = ($chk & 0x1ffffff) << 5 ^ $values[$i];

            for ($j = 0; $j < 5; $j++) {
                $value = (($top >> $j) & 1) ? self::GENERATOR[$j] : 0;
                $chk ^= $value;
            }
        }

        return $chk;
    }

    /**
     * Converts words of $fromBits bits to $toBits bits in size.
     *
     * @param int[] $data Character array of data to convert
     * @param int $inLen Number of elements in array
     * @param int $fromBits Word (bit count) size of provided data
     * @param int $toBits Requested word size (bit count)
     * @param bool $pad Whether to pad (only when encoding)
     * @return int[]
     * @throws Bech32Exception
     */
    private function convertBits(array $data, int $inLen, int $fromBits, int $toBits, bool $pad = true)
    {
        $acc = 0;
        $bits = 0;
        $ret = [];
        $maxv = (1 << $toBits) - 1;
        $maxacc = (1 << ($fromBits + $toBits - 1)) - 1;

        for ($i = 0; $i < $inLen; $i++) {
            $value = $data[$i];

            if ($value < 0 || $value >> $fromBits) {
                throw new Exception('Invalid value for convert bits');
            }

            $acc = (($acc << $fromBits) | $value) & $maxacc;
            $bits += $fromBits;

            while ($bits >= $toBits) {
                $bits -= $toBits;
                $ret[] = (($acc >> $bits) & $maxv);
            }
        }

        if ($pad && $bits) {
            $ret[] = ($acc << $toBits - $bits) & $maxv;
        } elseif ($bits >= $fromBits || ((($acc << ($toBits - $bits))) & $maxv)) {
            throw new Exception('Invalid data');
        }

        return $ret;
    }

    /**
     * @param int $version
     * @param string $program
     * @throws Bech32Exception
     */
    private function validateWitnessProgram(int $version, string $program)
    {
        if ($version < 0 || $version > 16) {
            throw new Exception("Invalid witness version");
        }

        $sizeProgram = strlen($program);
        if ($version === 0) {
            if ($sizeProgram !== 20 && $sizeProgram !== 32) {
                throw new Exception("Invalid size for V0 witness program");
            }
        }

        if ($sizeProgram < 2 || $sizeProgram > 40) {
            throw new Exception("Witness program size was out of valid range");
        }
    }

    private function getEncoding($encoding)
    {
        if ($encoding === self::BECH32) {
            return 1;
        }

        if ($encoding === self::BECH32M) {
            return 0x2bc830a3;
        }

        return null;
    }
}



function isBech32($address)
    {
        $prefix = "ocv";
        $expr = sprintf(
            '/^((%s)(0([ac-hj-np-z02-9]{39}|[ac-hj-np-z02-9]{59})|1[ac-hj-np-z02-9]{8,87}))$/',
            $prefix
        );

        if (preg_match($expr, $address, $match) === 1) {
            try {
                $bech32 = new Bech32;
                $bech32->decodeSegwit($match[2], $match[0], Bech32::BECH32);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }
	
	function base64url_encode($data)
{
  // First of all you should encode $data to Base64 string
  $b64 = base64_encode($data);

  // Make sure you get a valid result, otherwise, return FALSE, as the base64_encode() function do
  if ($b64 === false) {
    return false;
  }

  // Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
  $url = strtr($b64, '+/', '-_');

  // Remove padding character from the end of line and return the Base64URL result
  return rtrim($url, '=');
}





function rpc_query($method,$params=array()) {


global $parse_ocvcoin_conf_file;


$rpcuser = $parse_ocvcoin_conf_file["main"]["rpcuser"];
$rpcpassword = $parse_ocvcoin_conf_file["main"]["rpcpassword"];
$rpcurl = 'http://'.$rpcuser.':'.$rpcpassword.'@127.0.0.1:'.$parse_ocvcoin_conf_file["main"]["rpcport"].'/';

$random_string = base64url_encode(random_bytes(16));


$data = array (
  'jsonrpc' => '1.0',
  'id' => $random_string,
  'method' => $method,
  'params' => $params, 
  
);


$options = array(
    'http' => array(
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
    ),
);
$context  = stream_context_create($options);
$result = file_get_contents($rpcurl, false, $context);


$response = json_decode($result, true);
if (!is_array($response)) 
	return false;




if($response["id"] !== $random_string){
	
	var_dump($data,$random_string);
	echo "
	
	rpc ret id mismatch!
	
	";
	return false;
	
}
if(!empty($response["error"])){
	
	var_dump($data,$response);
	echo "
	
	err not empty!
	
	";
	return false;
	
}



return $response["result"];

}
