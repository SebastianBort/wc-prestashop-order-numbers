<?php 
/*
Plugin Name: PrestaShop Order Ids
Description: Numery zamówienia na wzór Prestashop, idntyfikator zamówienia skladaja się z losowego ciągu liter.
Version: 1.0
Author: Sebastian Bort
*/

class PrestaShop_Like_Order_Ids {

      const order_key = 'custom_order_id';
      const id_length = 9;
      
      public function __construct()  {
      
          add_action('wp_insert_post', [$this, 'insert_custom_order_id'], 999);
          add_filter('woocommerce_order_number', [$this, 'display_custom_order_id'], 999, 2);
          add_action('pre_get_posts', [$this, 'search_by_custom_order_id']);
      }
      
       public function search_by_custom_order_id($query) {
			
            if(!is_admin() || !isset($query->query['s']) || $query->query['post_type'] !== 'shop_order') {
				return false;
			}
 
			$query->set('meta_key', self::order_key);
			$query->set('meta_value', $query->query['s']);
            $query->set('s', null);
	   }
      
      public function display_custom_order_id($order_number, $order) {
      
            $custom_order_id = get_post_meta($order->get_id(), self::order_key, true);        
            return !empty($custom_order_id) ? $custom_order_id : $order_number;
      }
      
      public function insert_custom_order_id($order_id) {  
          
            if(!in_array(get_post_type($order_id) , ['shop_order','shop_subscription'], true)) {
                return false;
            }
        
            $custom_order_id = $this->generate_custom_order_id();
            update_post_meta($order_id, self::order_key, $custom_order_id);        
      }
      
      public function generate_custom_order_id() {
      
            $existing_ids = $this->get_all_orders_ids();       
            do {
                $custom_order_id = $this->generate_random_id();
            }
            while(in_array($custom_order_id, $existing_ids));
        
            return $custom_order_id;
      }
      
      private function get_all_orders_ids() {
      
            global $wpdb;
            $sql = sprintf("SELECT DISTINCT `meta_value` FROM `%s` WHERE `meta_key` = '%s'", $wpdb->postmeta, self::order_key);
      
            $output = [];
            $results = $wpdb->get_results($sql);
            
            if(!empty($results)) {
                foreach ($results AS $row) {
                    array_push($output, $row->meta_value);
                }
            }
            return $output;
      }
      
      private function generate_random_id() {
              
              $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
              $chars_count = strlen($chars);
              
              $random_id = '';
              
              for($i = 0; $i < self::id_length; $i++) {
                  $random_id .= $chars[rand(0, $chars_count - 1)];
              }
              return $random_id;
      }
}
    
new PrestaShop_Like_Order_Ids();   

?>