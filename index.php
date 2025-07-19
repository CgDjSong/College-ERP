<?php
define('IN_SS', true);
include_once('./inc/init.php');

$title = '';
$folder = [];
$pid = $ss->get_input('pid', 1);
$page = isset($ss->input['page']) ? (int)$ss->input['page'] : 1;
$sort = isset($ss->input['sort']) ? $ss->input['sort'] : $ss->settings['sort'];

if($pid != 0)
{
$query = $db->simple_select("files", "fid, name, use_icon, path, description", "fid='{$pid}'");
$folder = $db->fetch_array($query);

if(!is_array($folder))
{
header('Location: '.$ss->settings['url']);
exit;
}

$title = $folder['name'];
$folder['name'] = escape($folder['name']);
}
else
{
$folder['name'] = 'Home';
$folder['use_icon'] = 0;
}

include_once('./header.php');

// Comingsoon
if($pid == 0)
{
$options = ['order_by' => 'cid', 'order_dir' => 'desc'];

$query = $db->simple_select("comingsoon", "description", "status='A'", $options);
$total = $db->num_rows($query);

if($total != 0)
{
echo '<h2>Coming Soon</h2>
<div class="updates">';

while($soon = $db->fetch_array($query))
{
echo '<div>'.$soon['description'].'</div>';
}
echo '</div>';
}
}

if($ss->settings['show_searchbox'])
{
echo '<div class="search">
<form method="post" action="'.$ss->settings['url'].'/files/search.html">
<input type="text" name="find" value="" size="20" />
<input type="hidden" name="pid" value="'.$pid.'" />
<input type="hidden" name="action" value="do_search" />
<input type="submit" value="Search" />
</form>
</div>';
}

// Updates
if($pid == 0)
{
echo '<h2>Latest Updates</h2>
<div class="updates">';
$options = ['order_by' => 'uid', 'order_dir' => 'desc', 'limit' => $ss->settings['updates_on_index']];

$query = $db->simple_select("updates", "description", "status='A'", $options);
$total = $db->num_rows($query);

if($total != 0)
{
while($update = $db->fetch_array($query))
{
echo '<div>'.$update['description'].'</div>';
}
}
else
{
echo '<div>No updates!</div>';
}

echo '<div><a href="'.$ss->settings['url'].'/latest_updates/1.html">[More Updates]</a></div>
</div>';
}

include_once('./assets/ads/bcategory.php');

// Category title
if($pid == 0)
{
echo '<div id="category"><h2>Select Category</h2></div>';
}
else
{
echo '<div id="category"><h2>'.$folder['name'].'</h2></div>
<div class="description">'.$folder['description'].'</div>';

if(file_exists(SS_ROOT.'/thumbs/'.$folder['fid'].'.png'))
{
echo '<div class="showimage" align="center"><img src="'.$ss->settings['url'].'/thumbs/'.$folder['fid'].'.png" alt="'.$folder['name'].'" height="150" width="150" class="absmiddle"/></div>';
}
}

include_once('./assets/ads/acategory.php');

$sort_links = [['value' => 'new2old', 'name' => 'New'], ['value' => 'a2z', 'name' => 'A to Z'], ['value' => 'z2a', 'name' => 'Z to A'], ['value' => 'download', 'name' => 'Download']];

switch($sort)
{
case 'a2z':
$order = 'name ASC';
break;
case 'z2a':
$order = 'name DESC';
break;
case 'download':
$order = 'dcount DESC';
break;
default:
$order = 'time DESC';
break;
}

if($pid != 0)
{
echo '<div class="dtype">';

$bar = '';

foreach($sort_links as $sort_link)
{
if($sort_link['value'] == $sort)
{
echo ''.$bar.'<a href="'.$ss->settings['url'].'/categorylist/'.$folder['fid'].'/'.$sort_link['value'].'/'.$page.'/'.convert_name($folder['name']).'.html" class="active">'.$sort_link['name'].'</a>';
}
else
{
echo ''.$bar.'<a href="'.$ss->settings['url'].'/categorylist/'.$folder['fid'].'/'.$sort_link['value'].'/'.$page.'/'.convert_name($folder['name']).'.html">'.$sort_link['name'].'</a>';
}

$bar = ' | ';
}

echo '</div>';
}

include_once('./assets/ads/bfilelist.php');

$query = $db->simple_select("files", "fid", "pid='{$pid}'");
$total = $db->num_rows($query);

if($total != 0)
{
$start = ($page-1)*$ss->settings['files_per_page'];

$options = ['order_by' => 'isdir DESC, disporder ASC, '.$order.'', 'limit_start' => $start, 'limit' => $ss->settings['files_per_page']];

$query = $db->simple_select("files", "fid, name, isdir, tag, path, size, dcount", "pid='{$pid}'", $options);
while($file = $db->fetch_array($query))
{
if($file['isdir'] == 1)
{
echo '<div class="catRow"><a href="'.$ss->settings['url'].'/categorylist/'.$file['fid'].'/'.convert_name($file['name']).'.html"><div>'.escape($file['name']).'';

if($ss->settings['show_filecount'])
{
$counter = $db->simple_select("files", "fid", "path LIKE '".$db->escape_string_like($file['path'])."%' AND `isdir` = '0'");
echo ' ['.$db->num_rows($counter).'] ';
}

if($file['tag'] == 1)
{
echo ' '.ss_img('new.png', "New").'';
}
else if($file['tag'] == 2)
{
echo ' '.ss_img('updated.png', "Updated").'';
}

echo '</div></a></div>';
}
else
{
echo '<div class="fl"><a href="'.$ss->settings['url'].'/download/'.$file['fid'].'/'.convert_name($file['name']).'.html" class="fileName"><div><div>';

if(file_exists(SS_ROOT.'/thumbs/'.$file['fid'].'.png'))
{
echo '<img src="'.$ss->settings['url'].'/thumbs/'.$file['fid'].'.png" alt="'.escape($file['name']).'" width="80" height="80" />';
}
else if($folder['use_icon'] == 1 && file_exists(SS_ROOT.'/thumbs/'.$folder['fid'].'.png'))
{
echo '<img src="'.$ss->settings['url'].'/thumbs/'.$folder['fid'].'.png" alt="'.escape($file['name']).'" width="80" height="80" />';
}
else 
{
echo '<img src="'.$ss->settings['url'].'/icon.php?file='.base64_encode($file['path']).'&fid='.$file['fid'].'" alt="'.escape($file['name']).'" width="80" height="80" />';
}

echo '</div><div>'.escape($file['name']).'';

if($file['tag'] == 1)
{
echo ' '.ss_img('new.png', "New").'';
}
else if($file['tag'] == 2)
{
echo ' '.ss_img('updated.png', "Updated").'';
}

echo '<br /><span>['.convert_filesize($file['size']).']</span><br /><span>'.$file['dcount'].' Download</span></div></div></a></div>';
}
}

$url = "{$ss->settings['url']}/categorylist/{$pid}/{$sort}/{page}/".convert_name($folder['name']).".html";

echo pagination($page, $ss->settings['files_per_page'], $total, $url);
}
else
{
echo '<div class="catRow">Folder is empty!</div>';
}

include_once('./assets/ads/afilelist.php');

// Services
if($pid == 0)
{
include_once('./assets/services.php');
}

if($pid != 0)
{
echo '<h2>Related Tags</h2>
<div><span style="font-size:10px;"><span style="color:#006400;">Tags :</span> '.$folder['name'].' Download, '.$folder['name'].' Free Download, '.$folder['name'].' All Mp3 Song Download, '.$folder['name'].' Movies Full Mp3 Songs, '.$folder['name'].' video song download, '.$folder['name'].' Mp4 HD Video Song Download, '.$folder['name'].' Download Ringtone, '.$folder['name'].' Movies Free Ringtone, '.$folder['name'].' Movies Wallpapers, '.$folder['name'].' HD Video Song Download</div>';
}

if($pid != 0)
{

$_dr = '';

echo '<div class="path"><a href="'.$ss->settings['url'].'/">Home</a>';

foreach(explode('/', substr($folder['path'], 7)) as $dr)
{
$_dr .= "/".$dr;
$path = "/files{$_dr}";

$query = $db->simple_select("files", "fid, name", "path='".$db->escape_string($path)."'");
$id = $db->fetch_array($query);

if($pid == $id['fid'])
{
echo ' &#187; '.escape($id['name']).'';
}
else
{
echo ' &#187; <a href="'.$ss->settings['url'].'/categorylist/'.$id['fid'].'/'.$ss->settings['sort'].'/1.html">'.escape($id['name']).'</a>';
}
}
echo '</div>';
}

include_once('./footer.php');
