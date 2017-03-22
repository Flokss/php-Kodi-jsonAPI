<?php
/*

https://github.com/KiboOst/php-Kodi-jsonAPI

*/

class Kodi{

	public $_version = "0.2";

	//user functions======================================================
	//GET
	public function getActivePlayer()
	{
		$jsonString = '{"method":"Player.GetActivePlayers"}';
		$answer = $this->_request($jsonString);
		if (isset($answer['error']) ) return array('result'=>null, 'error'=>$answer['error']);

		if (count($answer['result'])>0)
		{
			$this->_playerid = $answer['result'][0]['playerid'];
			$this->_playerType = $answer['result'][0]['type'];
			return $this->_playerid;
		}
		return array('error'=>'No active player.');
	}

	public function getPlayerItem($playerid=null)
	{
		if ( !isset($playerid) ) $playerid = $this->getActivePlayer();
		if ( is_array($playerid) ) return $playerid;

		$jsonString = '{
						"method":"Player.GetItem",
						"params":{
									"properties": ["title", "album", "artist", "duration", "file"],
									"playerid": '.$playerid.'
								}
						}';

		return $this->_request($jsonString);
	}

	public function getPlayList($playlistid=null) //0: music, 1: video, 2:picture
	{
		if ( !isset($playlistid) ) $playlistid = $this->getActivePlayer();
		if ( is_array($playlistid) ) $playlistid = 0;

		if ($playlistid == 0)
		{
			$jsonString = '{"method":"Playlist.GetItems",
						"params":{
									"properties": ["title", "album", "artist", "duration"],
									"playlistid": 0 }
						}';
		}
		else
		{
			$jsonString = '{"method":"Playlist.GetItems",
						"params":{
									"properties": ["runtime", "showtitle", "season", "title", "artist"],
									"playlistid": 1 }
						}';
		}

		return $this->_request($jsonString);
	}

	public function getDirectory($folder, $type=0)
	{
		$folder = urlencode($folder);

		if ($type == 0) $type = 'music';
		if ($type == 1) $type = 'video';
		if ($type == 2) $type = 'picture';

		$jsonString = '{"method":"Files.GetDirectory",
						"params":{"directory":"'.$folder.'",
						"media":"'.$type.'"
						}}';

		return $this->_request($jsonString);
	}

	public function getVolume()
	{
		$jsonString = '{
						"method":"Application.GetProperties",
						"params":{"properties": ["volume"]}
						}';

		return $this->_request($jsonString);
	}

	public function getTime($playerid=null) { return $this->PlayerGetProperties('time', $playerid); }
	public function getShuffle($playerid=null) { return $this->PlayerGetProperties('shuffled', $playerid); }
	public function getRepeat($playerid=null) { return $this->PlayerGetProperties('repeat', $playerid); }

	//SET
	public function play($playlistid=null)
	{
		if ( !isset($playlistid) ) $playlistid = $this->getActivePlayer();
		if ( is_array($playlistid) ) $playlistid = 0;

		$jsonString = '{
						"method":"Player.Open",
						"params":{ "item": { "playlistid": '.$playlistid.', "position": 0 } }
						}';

		return $this->_request($jsonString);
	}

	public function stop($playerid=null)
	{
		if ( !isset($playerid) ) $playerid = $this->getActivePlayer();
		if ( is_array($playerid) ) $playerid = 0;

		$jsonString = '{
						"method":"Player.Stop",
						"params":{"playerid":'.$playerid.'}
						}';

		return $this->_request($jsonString);
	}

	public function openFile($file) //will always answer OK even if file doesn't exist!!
	{
		$file = urlencode($file);
		$jsonString = '{"method":"Player.Open",
						"params":{"item":{"file":"'.$file.'"}}}';

		return $this->_request($jsonString);
	}

	public function openDirectory($folder)
	{
		$folder = urlencode($folder);
		$jsonString = '{"method":"Player.Open",
						"params":{"item":{"directory":"'.$folder.'"}}}';

		return $this->_request($jsonString);
	}

	public function clearPlayList($playlistid=null)
	{
		if ( !isset($playlistid) ) $playlistid = $this->getActivePlayer();
		if ( is_array($playlistid) ) $playlistid = 0;

		$jsonString = '{"method":"Playlist.Clear",
						"params":{"playlistid":'.$playlistid.'}
						}';

		return $this->_request($jsonString);
	}

	public function loadPlaylist($playlist, $type=0)
	{
		$playlist = urlencode($playlist);

		if ($type == 0) $media = 'music';
		if ($type == 1) $media = 'video';
		if ($type == 2) $media = 'picture';

		$jsonString = '{"method": "Playlist.Add",
						"params":{"playlistid":'.$type.',
								  "item":{"directory": "'.$playlist.'", "media": "'.$media.'"}
								}
						}';

		return $this->_request($jsonString, 30);
	}

	public function addPlayListDir($folder=null, $playlistid=null)
	{
		$folder = urlencode($folder);

		if ( !isset($playlistid) ) $playlistid = $this->getActivePlayer();
		if ( is_array($playlistid) ) $playlistid = 0;

		$jsonString = '{"method":"Playlist.Add",
						"params":{
								"playlistid":'.$playlistid.', "item": {"directory":"'.$folder.'"}
								}
						}';

		return $this->_request($jsonString, 30);
	}

	public function addPlayListFile($file=null, $playlistid=null)
	{
		$file = urlencode($file);

		if ( !isset($playlistid) ) $playlistid = $this->getActivePlayer();
		if ( is_array($playlistid) ) $playlistid = 0;

		$jsonString = '{"method":"Playlist.Add",
						"params":{
								"playlistid":'.$playlistid.', "item": {"file":"'.$file.'"}
								}
						}';

		return $this->_request($jsonString);
	}

	public function togglePlayPause($playerid=null)
	{
		if ( !isset($playerid) ) $playerid = $this->getActivePlayer();
		if ( is_array($playerid) ) return $playerid;

		$jsonString = '{"method":"Player.PlayPause",
						"params":{
									"playerid": '.$playerid.'
								}
						}';

		return $this->_request($jsonString);
	}

	public function setShuffle($value=true, $playerid=null) //"true" / "false"
	{
		if ( !isset($playerid) ) $playerid = $this->getActivePlayer();
		if ( is_array($playerid) ) return $playerid;

		$set = ( ($value == true) ? 'true' : 'false' );

		$jsonString = '{"method":"Player.SetShuffle",
						"params":{
									"playerid": '.$playerid.',
									"shuffle":'.$set.'
								}
						}';

		return $this->_request($jsonString);
	}

	public function setRepeat($value="all", $playerid=null) //one, all, off
	{
		if ( !isset($playerid) ) $playerid = $this->getActivePlayer();
		if ( is_array($playerid) ) return $playerid;

		$jsonString = '{"method":"Player.SetRepeat",
						"params":{
									"playerid": '.$playerid.',
									"repeat":"'.$value.'"
								}
						}';

		return $this->_request($jsonString);
	}

	public function setVolume($level=30)
	{
		$jsonString = '{"method":"Application.SetVolume",
						"params":{"volume":'.$level.'}
						}';

		return $this->_request($jsonString);
	}

	public function volumeInc()
	{
		$jsonString = '{"method":"Application.SetVolume",
				"params":{"volume": "increment"}
				}';

		return $this->_request($jsonString);
	}

	public function volumeDec()
	{
		$jsonString = '{"method":"Application.SetVolume",
				"params":{"volume": "decrement"}
				}';

		return $this->_request($jsonString);
	}

	//System
	public function reboot() { return $this->_request('{"method":"System.Reboot"}'); }
	public function hibernate() { return $this->_request('{"method":"System.Hibernate"}'); }
	public function shutdown() { return $this->_request('{"method":"System.Shutdown"}'); }
	public function suspend() { return $this->_request('{"method":"System.Suspend"}'); }


	//internal functions==================================================
	protected function PlayerGetProperties($prop, $playerid)
	{
		$currentPlayer = $this->getActivePlayer();
		if ( !isset($playerid) ) $playerid = $currentPlayer;
		if ( is_array($playerid) ) return $playerid;

		if ( $currentPlayer != $playerid ) return array('error'=>"Player ID ".$playerid." isn't active!");

		$jsonString = '{"method":"Player.GetProperties",
						"params":{"properties": ["'.$prop.'"], "playerid": '.$playerid.'}
						}';

		return $this->_request($jsonString);
	}

	//calling functions===================================================
	public function sendJson($jsonString, $timeout=3) //testing and custom json request purpose
	{
		return $this->_request($jsonString, $timeout);
	}

	protected function _request($jsonString, $timeout=3)
	{
		if (!isset($this->_curlHdl))
		{
			$this->_curlHdl = curl_init();
			curl_setopt($this->_curlHdl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->_curlHdl, CURLOPT_FOLLOWLOCATION, true);

			curl_setopt($this->_curlHdl, CURLOPT_CONNECTTIMEOUT, 3);
			curl_setopt($this->_curlHdl, CURLOPT_TIMEOUT, 3);
		}

		curl_setopt($this->_curlHdl, CURLOPT_TIMEOUT, $timeout);

		$json = json_decode($jsonString, true);
		$json['jsonrpc'] = '2.0';
		$json['id'] = $this->_POSTid;
		$this->_POSTid++;

		$url = "http://".$this->_IP."/jsonrpc?request=".json_encode($json);
		curl_setopt($this->_curlHdl, CURLOPT_URL, $url);

		$answer = curl_exec($this->_curlHdl);
		if(curl_errno($this->_curlHdl))
		{
		    return array('error'=>curl_error($this->_curlHdl));
		}

		if ($answer == false)
		{
			return array('error'=>"Couldn't reach Kodi device.");
		}

		$answer = json_decode($answer, true);
		if (isset($answer['error']) ) return array('result'=>null, 'error'=>$answer['error']);
		return array('result'=>$answer['result']);
	}

	function __construct($IP)
	{
		$IP = str_replace('http://', '', $IP);
		$this->_IP = $IP;

		$var = $this->getActivePlayer();
		if (isset($var['error']) ) $this->_error = $var['error'];
	}

	public $_IP;
	public $_error;
	public $_playerid;
	public $_playerType;

	protected $_curlHdl = null;
	protected $_POSTid = 0;

	/*
	playerid 0: music
	palyerid 1: video
	palyerid 2: picture

	playlist 0: current music playlist
	playlist 1: current video playlist

	http://kodi.wiki/view/JSON-RPC_API/v8

	*/

//Kodi end
}

?>
