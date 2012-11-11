/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	config.uiColor = '#6c81c6';
	config.toolbar = [
		{ name: 'document',    items : [ 'Source','-','Save','DocProps','Preview','-','Templates' ] },
		{ name: 'clipboard',   items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
		{ name: 'editing',     items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
		{ name: 'styles',      items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat', 'Format' ] },
		{ name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Blockquote' ] },
		{ name: 'links',       items : [ 'Link','Unlink','Anchor' ] },
		{ name: 'insert',      items : [ 'Image','Table','HorizontalRule','SpecialChar','PageBreak' ] },
		{ name: 'tools',       items : [ 'Maximize', 'ShowBlocks','-','About' ] }
	];
	config.contentsCss = '../style.css';
};
