<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
// 2016-02-01
class Stats {
    
    public $stats_table = 'stats_products';
    public $item_table = 'shop_products';
    public $item_table_field = 'views';
    public $item_id_field = 'id';
    public $ci;

    public function __construct($params = array())
    {
        $this->ci =& get_instance();
        $this->ci->load->library('user_agent');
        // set params
        if(!empty($params)){
            $this->stats_table = (!empty($params['stats_table'])) ? $params['stats_table'] : 'stats_products';
            $this->item_table = (!empty($params['item_table'])) ? $params['item_table'] : 'shop_products';
            $this->item_table_field = (!empty($params['item_table_field'])) ? $params['item_table_field'] : 'views';
            $this->item_id_field = (!empty($params['item_id_field'])) ? $params['item_id_field'] : 'id';
        }
    }
    
    public function set_stats($item_id)
    {
        if(!$this->ci->agent->is_browser()) return; // visits of bots is not counted!
        
        if(!empty($item_id)){
            // set item stats
            $this->set_item_stats($item_id);
            // set full stats
            $this->set_full_stats($item_id);
        }
    }
    
    public function set_item_stats($id)
    {
        if(!empty($id)){
            $this->ci->db->query("UPDATE `" . $this->item_table . "` SET `" . $this->item_table_field . "` = (`" . $this->item_table_field . "` + 1) WHERE `" . $this->item_id_field . "` = '" . intval($id) . "'");
        }
    }
    
    public function set_full_stats($id)
    {
        $data = array(
            'user_ip' => $this->ci->input->ip_address(),
            'user_agent' => $this->ci->agent->agent_string(),
            'user_browser' => $this->ci->agent->browser(),
            'user_browser_version' => $this->ci->agent->version(),
            'user_mobile' => ($this->ci->agent->is_mobile()) ? 1 : 0,
            'user_device' => ($this->ci->agent->is_mobile()) ? $this->ci->agent->mobile() : 'PC',
            'user_platform' => $this->ci->agent->platform(),
            'item_id' => intval($id),
            'visit_date' => date('Y-m-d H:i:s'),
        );
        $this->ci->db->insert($this->stats_table, $data);
    }
    
    public function get_items($fields = '*', $count = 0, $per_page = 20, $offset = 0)
    {
        $return = (empty($count)) ? array() : 0;
        if($fields !== '*'){
            $this->ci->db->select($fields);
        }
        $this->ci->db->where($this->item_table_field . ' >', 0);
        if(empty($count)){
            $this->ci->db->order_by($this->item_table_field . ' desc, ' . $this->item_id_field . ' desc');
            $this->ci->db->limit((int)$per_page, (int)$offset);
            $return = $this->ci->db->get($this->item_table)->result_array();
        }
        else{
            $return = $this->ci->db->count_all_results($this->item_table);
        }
        return $return;
    }
    
    public function get_items_by($fields = '*', $count = 0, $period = 'week', $per_page = 20, $offset = 0)
    {
        $return = (empty($count)) ? array() : 0;
        $intervals = array(
            'today' => 'DATE(`visit_date`) = CURDATE()',
            'week' => '`visit_date` >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)',
            'month' => '`visit_date` >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)'
        );
        $where = (in_array($period, array_keys($intervals))) ? $intervals[$period] : $intervals['today'];
        
        if(empty($count)){
            // get products ID's
            $ids_res = $this->ci->db->query("SELECT `item_id`, COUNT(`item_id`) AS `cnt` FROM `" . $this->stats_table . "` WHERE " . $where . " GROUP BY `item_id` ORDER BY `cnt` DESC LIMIT " . (int)$offset . ", " . (int)$per_page)->result_array();
            
            if(!empty($ids_res)){
                $ids = array_to_simple($ids_res, 'item_id');
                
                if(!empty($ids)){
                    $return = $this->ci->db->select($fields)
                            ->where($this->item_table_field . ' >', 0)
                            ->where_in($this->item_id_field, $ids)
                            ->get($this->item_table)->result_array();
                    
                    if(!empty($return)){
                        // get views by period
                        $counts = $this->ci->db->query("SELECT `item_id`, COUNT(`item_id`) AS `cnt` FROM `" . $this->stats_table . "` WHERE `item_id` IN ('" . implode("', '", $ids) . "') AND " . $where . " GROUP BY `item_id`")->result_array();
                        $counts = array_by_index($counts, 'item_id');
                        
                        foreach ($return as $key => $row) {
                            $return[$key]['views'] = (!empty($counts[$row['id']])) ? $counts[$row['id']]['cnt'] : $row['views'];
                        }
                        $return = array_order_by($return, 'views', SORT_DESC);
                    }
                }
            }
        }
        else{
            $result = $this->ci->db->query("SELECT DISTINCT `item_id` FROM `" . $this->stats_table . "` WHERE " . $where)->result_array();
            $return = (!empty($result)) ? count($result) : 0;
        }
        return $return;
    }
    
    public function get_summary()
    {
        $return = array();
        $return['browsers'] = $this->ci->db->query("SELECT DISTINCT `user_browser`, COUNT(`user_browser`) AS `cnt` FROM `" . $this->stats_table . "` GROUP BY `user_browser` ORDER BY `cnt` DESC")->result_array();
        $return['os'] = $this->ci->db->query("SELECT DISTINCT `user_platform`, COUNT(`user_platform`) AS `cnt` FROM `" . $this->stats_table . "` GROUP BY `user_platform` ORDER BY `cnt` DESC")->result_array();
        $return['devices'] = $this->ci->db->query("SELECT DISTINCT `user_device`, COUNT(`user_device`) AS `cnt` FROM `" . $this->stats_table . "` GROUP BY `user_device` ORDER BY `cnt` DESC")->result_array();
        $return['platforms']['pc'] = $this->ci->db->select('id')->where('user_mobile', 0)->count_all_results($this->stats_table);
        $return['platforms']['mobile'] = $this->ci->db->select('id')->where('user_mobile', 1)->count_all_results($this->stats_table);
        $return['num_res'] = $this->ci->db->count_all_results($this->stats_table);
        return $return;
    }
    
    public function get_chart_data($period = 'week')
    {
        $intervals = array(
            'today' => 'DATE(`visit_date`) = CURDATE()',
            'week' => '`visit_date` >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)',
            'month' => '`visit_date` >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)'
        );
        $where = (in_array($period, array_keys($intervals))) ? $intervals[$period] : $intervals['week'];
        
        $return = $this->ci->db->query("SELECT DATE_FORMAT(`visit_date`, '%Y-%c-%e') AS `date`, COUNT(`id`) AS `cnt`, `visit_date` FROM `" . $this->stats_table . "` WHERE " . $where . " GROUP BY `date` ORDER BY `visit_date` ASC")->result_array();
//                var_dump($this->ci->db->last_query(), $return);
        
        return $return;
    }
    
    public function get_chart_data_by($type = 'months', $year = '') 
    {
        $type = ($type == 'years') ? 'years' : 'months';
        $year = (!empty($year)) ? $year : date('Y');
        $where = array(
            'months' => array('where' => "YEAR(`visit_date`) = '" . $year . "'", 'date_format' => "DATE_FORMAT(`visit_date`, '%c')"),
            'years' => array('where' => '1 = 1', 'date_format' => "YEAR(`visit_date`)"),
        );
        
        $return = $this->ci->db->query("SELECT " . $where[$type]['date_format'] . " AS `date`, COUNT(`id`) AS `cnt` FROM `" . $this->stats_table . "` WHERE " . $where[$type]['where'] . " GROUP BY `date` ORDER BY `date` ASC")->result_array();
        $return = (!empty($return)) ? array_order_by($return, 'date', SORT_ASC) : array();
        
        return $return;
    }
    
    public function get_chart_by_day($date = '')
    {
        $date = (empty($date)) ? date('Y-m-d') : $date;
        $return = $this->ci->db->query("SELECT DATE_FORMAT(`visit_date`, '%k') AS `date`, COUNT(`id`) AS `cnt` FROM `" . $this->stats_table . "` WHERE DATE(`visit_date`) = '" . $date . "' GROUP BY `date` ORDER BY `date` ASC")->result_array();
        $return = (!empty($return)) ? array_order_by($return, 'date', SORT_ASC) : array();
        
        return $return;
    }
    
}

/* End of file Stats.php */