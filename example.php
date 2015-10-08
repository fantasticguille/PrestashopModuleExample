<?php
if (!defined('_PS_VERSION_'))
  exit;

class Example extends Module
{
	public function __construct()
  {
    $this->name = 'abmtarjetas';
    $this->tab = 'front_office_features';
    $this->version = '0.9';
    $this->author = 'Mi Tienda Online';
    $this->need_instance = 0;
    $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_); 
    $this->bootstrap = true;
 
    parent::__construct();
 
    $this->displayName = $this->l('ABM Tarjetas');
    $this->description = $this->l('Alta, Baja y Modificación de Tarjetas y Planes.');
 
    $this->confirmUninstall = $this->l('Estás seguro que deseas desinstalar?');
 
    if (!Configuration::get('MYMODULE_NAME'))      
      $this->warning = $this->l('No hay nombre asignado');
  }

  public function install()
	{
	  if (Shop::isFeatureActive())
   	 Shop::setContext(Shop::CONTEXT_ALL);
 
	  if (!parent::install() ||
	    !$this->registerHook('leftColumn') ||
	    !$this->registerHook('header') ||
	    !Configuration::updateValue('MYMODULE_NAME', 'ABM Tarjetas')
	  )
	    return false;
	  return true;	}

	public function uninstall()
	{
	  if (!parent::uninstall() ||
	    !Configuration::deleteByName('MYMODULE_NAME')
	  )
	    return false;
	  return true;
	}


	public function getContent()
	{
	    $output = null;
	 
	    if (Tools::isSubmit('submit'.$this->name))
	    {
	        $my_module_name = strval(Tools::getValue('MYMODULE_NAME'));
	        if (!$my_module_name
	          || empty($my_module_name)
	          || !Validate::isGenericName($my_module_name))
	            $output .= $this->displayError($this->l('Invalid Configuration value'));
	        else
	        {
	            Configuration::updateValue('MYMODULE_NAME', $my_module_name);
	            $output .= $this->displayConfirmation($this->l('Settings updated'));
	        }
	    }
	    return $output.$this->displayForm();
	}

	public function displayForm()
	{
		$this->html = '';

		
//** HelperForm **//

		// Get default language
	    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
	     
	    // Init Fields form array
	    $fields_form[0]['form'] = array(
	        'legend' => array(
	            'title' => $this->l('Settings'),
	        ),
	        'input' => array(
	            array(
	                'type' => 'text',
	                'label' => $this->l('Configuration value'),
	                'name' => 'MYMODULE_NAME',
	                'size' => 20,
	                'required' => true
	            )
	        ),
	        'submit' => array(
	            'title' => $this->l('Save'),
	            'class' => 'button'
	        )
	    );
	     
	    $helper = new HelperForm();
	     
	    // Module, token and currentIndex
	    $helper->module = $this;
	    $helper->name_controller = $this->name;
	    $helper->token = Tools::getAdminTokenLite('AdminModules');
	    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
	     
	    // Language
	    $helper->default_form_language = $default_lang;
	    $helper->allow_employee_form_lang = $default_lang;
	     
	    // Title and toolbar
	    $helper->title = $this->displayName;
	    $helper->show_toolbar = true;        // false -> remove toolbar
	    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
	    $helper->submit_action = 'submit'.$this->name;
	    $helper->toolbar_btn = array(
	        'save' =>
	        array(
	            'desc' => $this->l('Save'),
	            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
	            '&token='.Tools::getAdminTokenLite('AdminModules'),
	        ),
	        'back' => array(
	            'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
	            'desc' => $this->l('Back to list')
	        )
	    );
	     
	    // Load current value
	    $helper->fields_value['MYMODULE_NAME'] = Configuration::get('MYMODULE_NAME');
	     
	    $this->html .= $helper->generateForm($fields_form);

//** HelperList **//

		$this->fields_list = array(
		    'id_category' => array(
		        'title' => $this->l('Id'),
		        'width' => 140,
		        'type' => 'text',
		    ),
		    'name' => array(
		        'title' => $this->l('Name'),
		        'width' => 140,
		        'type' => 'text',
		    ),
		);
		$helperList = new HelperList();
		 
		$helperList->shopLinkType = '';
		 
		$helperList->simple_header = true;
		 
		// Actions to be displayed in the "Actions" column
		$helperList->actions = array('edit', 'delete', 'view');
		 
		$helperList->identifier = 'id_category';
		$helperList->show_toolbar = true;
		$helperList->title = 'HelperList';
		$helperList->table = 'product_promoted_form_list';
		//echo $this->name.'_categories';


		//token
		$helperList->token = Tools::getAdminTokenLite('AdminModules');
		$helperList->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		$products = Product::getProducts($this->context->language->id, 0, '','id_product','ASC');

		$this->html .= $helperList->generateList($products, $this->fields_list);


		return $this->html;
	}
}
