<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
// include git utils, they should be located on same directory with syntax.php
require_once('git-utils.inc.php');

function find_end($string, $offset = 0)
{
    if($offset >= strlen($string))
        return FALSE;
    for($i = $offset; $i < strlen($string); $i++)
    {
        if(!is_numeric($string[$i]))
            return $i;
    }
    return strlen($string);
}

class syntax_plugin_dokugitviewer extends DokuWiki_Syntax_Plugin {
 
    function getType(){
        return 'substition';
    }
 
    function getSort(){
        return 999;
    }
 
    function connectTo($mode) {
      	$this->Lexer->addSpecialPattern('<dokugitviewer:.+?>',$mode,'plugin_dokugitviewer');
    }
 
 
    function handle($match, $state, $pos, &$handler)
	{
		$start = strlen('<dokugitviewer:');
		$end = -1;
		$params = substr($match, $start, $end);
		$params = preg_replace('/\s{2,}/', '', $params);
		$params = preg_replace('/\s[=]/', '=', $params);
		$params = preg_replace('/[=]\s/', '=', $params);
		//echo $params.'<br>';

		$return = array();
		foreach(explode(' ', $params) as $param)
		{
			$val = explode('=', $param);
			$return[$val[0]] = $val[1];
		}
        return $return;
    }
 
    function render($mode, &$renderer, $data) {
		$elements = array('ft' => 'features',
						  'bug' => 'bugs');
        if($mode == 'xhtml'){
			if(isset($data['repository']))
			{
				if(isset($data['limit']) && is_numeric($data['limit']))
					$limit = (int)($data['limit']);
				else
					$limit = 10;
			        if (empty($data['bare']))
			          $bare=false;
			        else
			          $bare=true;
				$log = git_get_log($data['repository'], $limit,$bare, $data['start'], $data['end']);
				
				$renderer->doc .=  "<u><strong> ".$data['start']." -> ".$data['end']."</strong></u>:<br/>";

				//$renderer->doc .= '<ul class="dokugitviewer">';
				$renderer->doc .= <<<CCC
				<table>
					<tr style="font-weight:bold;text-decoration:underline" align=left> 
						<td width=100> author</td>
						<td width=140> date</td>
						<td> commit</td>
					</tr>
CCC;
				
				foreach($log as $row)
				{
					$message = $row['message'];
					/*
					$renderer->doc .= '<li>';
					for($index = 0; $index < strlen($message); $index++)
					{
						$char = $message[$index];
						if($char == '#')
                        {
                            foreach(array_keys($elements) as $element)
                            {
                                $cmp = '#'.$element;
                                $src = substr($message, $index, strlen($cmp));
                                if(strstr($src, $cmp))
                                {
                                    $key = substr($message, $index+1, strlen($cmp)-1);
                                    
                                    $src= substr($message, $index+1+strlen($key));
                                    $value = substr($src, 0, find_end($src));
                                    $index += strlen($element.$value); 
                                    $renderer->internallink($data[$elements[$element]].'#'.$element.$value, '#'.$element.$value);

                                }
                            }
						}
						else
							$renderer->doc .= $char;
					}
					$renderer->doc .= $message;
					//$renderer->doc .= '<strong>'.$message.'</strong>';
					//$renderer->doc .= '<br />';
					$renderer->doc .= ': ';
					$renderer->doc .= $row['author'].' on ';					
					$renderer->doc .= date(DATE_FORMAT,$row['timestamp']);
					$renderer->doc .= '</li>';
					*/
					$renderer->doc .= '<tr>';
					$renderer->doc .= '<td>'.$row['author'].'</td>';
					$renderer->doc .= '<td>'.date(DATE_FORMAT,$row['timestamp']).'</td>';
					$renderer->doc .= '<td>'.$message.'</td>';
					$renderer->doc .= '</tr>';
					
					
				}
				$renderer->doc .= '</table>';				
			}
            return true;
        }
        return false;
    }
}
 
?>

