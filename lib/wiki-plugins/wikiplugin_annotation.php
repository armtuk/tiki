<?php
// (c) Copyright 2002-2015 by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

function wikiplugin_annotation_info()
{
	return array(
		'name' => tra('Image Annotation'),
		'documentation' => 'PluginAnnotation',
		'description' => tra('Annotate an image'),
		'prefs' => array('wikiplugin_annotation'),
		'body' => tra('Autogenerated content. Leave blank initially.'),
		'filter' => 'striptags',
		'iconname' => 'edit',
		'introduced' => 2,
		'tags' => array( 'basic' ),
		'params' => array(
			'src' => array(
				'required' => true,
				'name' => tra('Location'),
				'description' => tra('Absolute URL to the image or relative path from Tiki site root.'),
				'filter' => 'url',
				'default' => '',
				'since' => '3.0',
			),
			'width' => array(
				'required' => true,
				'name' => tra('Width'),
				'description' => tra('Image width in pixels.'),
				'filter' => 'digits',
				'default' => '',
				'since' => '3.0',
			),
			'height' => array(
				'required' => true,
				'name' => tra('Height'),
				'description' => tra('Image height in pixels.'),
				'filter' => 'digits',
				'default' => '',
				'since' => '3.0',
			),
			'align' => array(
				'required' => false,
				'name' => tra('Alignment'),
				'description' => tra('Image alignment.'),
				'filter' => 'alpha',
				'advanced' => true,
				'default' => 'left',
				'since' => '2.0',
				'options' => array(
					array('text' => '', 'value' => ''), 
					array('text' => tra('Left'), 'value' => 'left'), 
					array('text' => tra('Right'), 'value' => 'right'), 
					array('text' => tra('Center'), 'value' => 'center'), 
				),
			),
		)
	);
}

function wikiplugin_annotation($data, $params)
{
	static $first = true;
	global $page, $tiki_p_edit;
	$headerlib = TikiLib::lib('header');

	$params = array_merge(array( 'align' => 'left' ), $params);

	$annotations = array();
	foreach ( explode("\n", $data) as $line ) {
		$line = trim($line);
		if ( empty( $line ) )
			continue;

		if ( preg_match("/^\(\s*(\d+)\s*,\s*(\d+)\s*\)\s*,\s*\(\s*(\d+)\s*,\s*(\d+)\s*\)(.*)\[(.*)\]$/", $line, $parts) ) {
			$parts = array_map('trim', $parts);
			list( $full, $x1, $y1, $x2, $y2, $label, $target ) = $parts;

			$annotations[] = array(
				'x1' => $x1,
				'y1' => $y1,
				'x2' => $x2,
				'y2' => $y2,
				'value' => $label,
				'target' => $target,
			);
		}
	}

	$annotations = json_encode($annotations);

	$headerlib->add_jsfile('lib/jquery_tiki/wikiplugin-annotation.js');

	static $uid = 0;
	$uid++;
	$cid = 'container-annotation-' . $uid;

	$labelSave = tra('Save changes to annotations');
	$message = tra('Image annotations changed.');
	
	if ( $tiki_p_edit == 'y' )
		$form = <<<FORM
<form method="post" action="tiki-wikiplugin_edit.php">
	<div style="display:none">
		<input type="hidden" name="page" value="$page"/>
		<input type="hidden" name="type" value="annotation"/>
		<input type="hidden" name="index" value="$uid"/>
		<input type="hidden" name="message" value="$message"/>
		<textarea id="$cid-content" name="content"></textarea>
	</div>
	<p><input type="submit" class="btn btn-default btn-sm" value="$labelSave"/></p>
</form>
FORM;
	else
		$form = '';

	// inititalise the annotations
	TikiLib::lib('header')->add_jq_onready('$("#' . $cid . '").imageAnnotation(' . $annotations . ');');

	$smarty = TikiLib::lib('smarty');
	$smarty->loadPlugin('smarty_function_icon');
	$minimize = smarty_function_icon(['name' => 'minimize'], $smarty);
	$delete = smarty_function_icon(['name' => 'delete'], $smarty);

	return <<<ANNOTATION
~np~
<div>
<div id="$cid" style="background:url({$params['src']}); width:{$params['width']}px; height:{$params['height']}px;position:relative">
	<div id="$cid-editor" style="display:none;width:250px;height:100px;position:absolute;background:white;border-color:black;border-style:solid;border-width:normal;padding:2px;">
		<a href="javascript:endEdit('$cid', false);void(0)">$minimize</a>
		<a href="javascript:handleDelete('$cid');void(0)" style="position:absolute;bottom:0px;right:0px;text-decoration:none;">$delete Delete</a>
		<form method="post" action="" onsubmit="endEdit('$cid',true);return false;">
			<div>Label</div>
			<div><input type="text" name="label" id="$cid-label" style="width:96%" onkeyup="handleCancel(event, '$cid')"/></div>
			<div style="display:none">Link</div>
			<div style="display:none"><input type="text" name="link" id="$cid-link" style="width:96%" onkeyup="handleCancel(event, '$cid')"/></div>
			<div><input type="submit" class="btn btn-default btn-sm" value="Save"/></div>
		</form>
	</div>
</div>
</div>
$form
~/np~
ANNOTATION;
}
