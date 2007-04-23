<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Weblinks
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.0
 */
class WeblinksViewWeblink extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		if($this->getLayout() == 'form') {
			$this->_displayForm($tpl);
			return;
		}

		//get the weblink
		$weblink =& $this->get('data');

		if ($weblink->url) {
			// redirects to url if matching id found
			$mainframe->redirect($weblink->url);
		}

		parent::display($tpl);
	}

	function _displayForm($tpl)
	{
		global $mainframe, $option;

		$db		=& JFactory::getDBO();
		$uri 	=& JFactory::getURI();
		$user 	=& JFactory::getUser();
		$model	=& $this->getModel();

		$lists = array();

		//get the weblink
		$weblink	=& $this->get('data');
		$isNew		= ($weblink->id < 1);

		// fail if checked out not by 'me'
		if ($model->isCheckedOut( $user->get('id') )) {
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'The weblink' ), $weblink->title );
			$mainframe->redirect( 'index.php?option='. $option, $msg );
		}

		// Set toolbar items for the page
		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Weblink' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		JToolBarHelper::help( 'screen.weblink.edit' );

		// Edit or Create?
		if (!$isNew)
		{
			$model->checkout( $user->get('id') );
		}
		else
		{
			// initialise new record
			$weblink->published = 1;
			$weblink->approved 	= 1;
			$weblink->order 	= 0;
			$weblink->catid 	= JRequest::getVar( 'catid', 0, 'post', 'int' );
		}

		// build the html select list for ordering
		$query = 'SELECT ordering AS value, title AS text'
			. ' FROM #__weblinks'
			. ' WHERE catid = ' . (int) $weblink->catid
			. ' ORDER BY ordering';
		$lists['ordering'] 			= JAdministratorHelper::SpecificOrdering( $weblink, $weblink->id, $query, 1 );

		// build list of categories
		$lists['catid'] 			= JAdministratorHelper::ComponentCategory( 'catid', $option, intval( $weblink->catid ) );
		// build the html select list
		$lists['published'] 		= JHTMLSelect::yesnoList( 'published', 'class="inputbox"', $weblink->published );

		//clean weblink data
		jimport('joomla.filter.output');
		JOutputFilter::objectHTMLSafe( $weblink, ENT_QUOTES, 'description' );

		$file 	= JPATH_ADMINISTRATOR .'/components/com_weblinks/models/weblink.xml';
		$params = new JParameter( $weblink->params, $file, 'component' );

		$this->assignRef('lists',		$lists);
		$this->assignRef('weblink',		$weblink);
		$this->assignRef('params',		$params);
		$this->assignRef('request_url',	$uri->toString());

		parent::display($tpl);
	}
}
?>
