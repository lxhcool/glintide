<?php
if (!class_exists('CSF_Field_hbgroup')) {
    class CSF_Field_hbgroup extends CSF_Fields
    {

        public function __construct($field, $value = '', $unique = '', $where = '', $parent = '')
        {
            parent::__construct($field, $value, $unique, $where, $parent);
        }

        public function render()
        {
    
            echo $this->field_before();         
            echo '<div class="ppo-header-builder-box">';
            echo '<div class="ppo-header-builder-item">';

            do_action('ppo_header_builder_area', array('id' => $this->field['id'],'value' => $this->value, 'prefix' => 'ppo_customizer'));
            
            echo '</div>';
            echo  '</div>';
            //var_dump($this->field);  
            //var_dump($this->value);    
            echo $this->field_after();
        }

    }
}