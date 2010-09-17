<?php defined('SYSPATH') or die('No direct script access.');


class Controller extends Controller_Core {

	//scenarios for validation
	public $form_scenarios = array();
	//rules for validation
	public $form_rules = array();
	//store fields for form validate
	public $form_fields = array();
  	//store errors after validation
	public $form_errors = array();
	//fields with validation rules to run validation on this array
	public $form_post = array();
	//standard image types, used in uploads
	public $image_types = array ('image/jpeg'=>'jpg','image/pjpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif');
	//standard vide types, used in uploads
	public $video_types = array ('video/x-flv'=>'flv','video/quicktime'=>'mov','video/x-ms-wmv'=>'wmv','video/mpeg'=>'mpg','video/mp4'=>'mp4');
	
	
	//currently logged user
	protected $user;
	
     public function __construct() {
        parent::__construct();
     }
    
    /**
     * make field for validation 
     *
     * @param   string   field name
     * @param   array   rules to validate
     * @param   array   validation scenario                    
     * @return  nothing.
     */ 
    public function make_field($field, $rules = array(), $scenario = array('*')) 
    {
         $this->form_fields[$field] = ''; //value for a view
         $this->form_rules[$field] = $rules; //rules for validation
         $this->form_errors[$field] = ''; //values for errors, default empty string, shown in view
         $this->form_scenarios[$field] = $scenario; //scenario for validation, default * (any)
    }
    
    /**
     * remove field added by make_field;
     *
     * @param   string   field name                   
     * @return  nothing.
     */ 
    public function remove_field($field) 
    {
         unset($this->form_fields[$field]);
         unset($this->form_rules[$field]);
         unset($this->form_errors[$field]);
         unset($this->form_scenarios[$field]);
    }

    /**
     * set fields values
     * set values for a view, both form field value and error message (default empty)
     *      
     * @param   string    submit field value to check if submit was pressed            
     * @return  nothing.
     */     
    public function set_fields($submit) 
    { 
         if (!empty($submit))  
         {           
            $this->form_fields = arr::overwrite($this->form_fields, $this->form_post->as_array());
            $this->form_errors = arr::overwrite($this->form_errors, $this->form_post->errors());
         }  
    }
    
    /**
     * populate form values after validation
     *
     * @param   bool   is submit pressed              
     * @return  nothing.
     */ 
    public function form_populate($submit) 
    { 
          $this->set_fields($submit);
          $this->template->center->form = $this->form_fields;
          $this->template->center->errors = $this->form_errors;
    }
    
    /**
     * populate form values after validation
     *
     * @param   bool   is submit pressed 
     * @param	 array
     * @param	 int		current record id			             
     * @return  nothing.
     */ 
    /*public function form_defaults($submit = FALSE, $fields = array(), $id = 0) 
    {
    		 if (empty($submit) AND !empty($fields) AND is_array($fields) AND (intval($id) === 0)) 
          	foreach ($fields as $key=>$value) 
          	{
	                  $this->form_fields[$key] = $value;
	          }
    }*/
    
    /**
     * populate form values after validation
     *
     * @param   bool   is submit pressed 
     * @param	 int		current record id
     * @param	 object	database model				             
     * @return  nothing.
     */
    public function form_database($submit = NULL, $id = 0, $model = NULL) 
    {      
          if (empty($submit) AND (intval($id) !== 0) AND is_object($model)) 
          {
               foreach ($this->form_fields as $key=>$value) 
               {
                       $this->form_fields[$key] = $model->$key;
               }
          }
    }

    /**
     * validate form 
     *
     * @param   string   scenario to validate              
     * @return  nothing.
     */ 
    public function validate($scenario = '') 
    {   //assign rules for custom scenario
          foreach ($this->form_rules as $field => $rules) 
          {
               if ( (in_array('*',$this->form_scenarios[$field])) OR (in_array($scenario,$this->form_scenarios[$field])) ) 
               {
                         foreach ($rules as $rule) 
                         {
                              $this->form_post->add_rules($field, $rule);
                         }
               }         
          }
          return $this->form_post->validate();
    }
} 