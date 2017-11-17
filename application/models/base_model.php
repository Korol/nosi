<?php
class Base_model extends CI_Model
{
    function __construct() {
        parent::__construct();
    }

    /**
     * @param string $table
     * @param string $fields
     * @param array $where
     * @param string $order_by
     * @param int $limit
     * @param int $offset
     * @return array
     */
    function get_list($table, $fields = '', $where = array(), $order_by = '', $limit = 0, $offset = 0){
        if(!empty($fields)) $this->db->select($fields);
        if(!empty($where)) $this->db->where($where);
        if(!empty($order_by)) $this->db->order_by($order_by);
        if(!empty($limit)) $this->db->limit($limit, $offset);
        return $this->db->get($table)
                        ->result_array();
        //return $this->db->last_query();
    }

    /**
     * @param string $table
     * @param string $fields
     * @param array $where
     * @param string $order_by
     * @param int $limit
     * @param int $offset
     * @return array
     */
    function get_new_list($table, $fields = '', $where = array(), $order_by = '', $limit = 0, $offset = 0){
        if(!empty($fields)) $this->db->select($fields);
        $this->db->join('goods_sync', 'goods_sync.good_articul = goods.articul');
        if(!empty($where)) $this->db->where($where);
        $order_by = 'goods_sync.good_balance desc';
        if(!empty($order_by)) $this->db->order_by($order_by);
        if(!empty($limit)) $this->db->limit($limit, $offset);
        return $this->db->get($table)
            ->result_array();
        //return $this->db->last_query();
    }

    /**
     * @param string $table
     * @param array $where
     * @return mixed
     */
    function count_rows($table, $where = array()){
        if(!empty ($where)) $this->db->where($where);
        return $this->db->count_all_results($table);
    }

    /**
     * @param string $table
     * @param array $fields
     * @param string $text
     * @param array $where
     * @param int $limit
     * @return mixed
     */
    function get_like_list($table, $fields, $text, $where = array(), $limit = 100){
        $text = trim($text);
        if(empty($text)) return FALSE;
        $this->db->limit($limit);

        $like = "";
        if(count($fields) > 1){
            for($i = 0; $i < count($fields); $i++){
                if($i == 0){
                    //$this->db->like($fields[$i], $text);
                    $like .= " ( " . $this->db->protect_identifiers($fields[$i]) . " LIKE '%" . $this->db->escape_like_str($text) . "%' ";
                }
                else{
                    //$this->db->or_like($fields[$i], $text);
                    $like .= " OR " . $this->db->protect_identifiers($fields[$i]) . " LIKE '%" . $this->db->escape_like_str($text) . "%' ";
                }
            }
            $like .= ") ";
            if(!empty($where)){
                $this->db->where($where);
            }
            $this->db->where($like, null, false);
            return $this->db->get($table)->result_array();
        }
        elseif(count($fields) == 1){
            $this->db->like($fields[0], $text);
            if(!empty($where)){
                $this->db->where($where);
            }
            return $this->db->get($table)->result_array();
        }
        else{
            return FALSE;
        }
    }

    /**
     * @param string $table
     * @param string $key
     * @param string $value
     * @param array $where
     * @return array
     */
    function get_select_list($table, $key, $value, $where = array()){
        $return = array();
        if(!empty($where)) $this->db->where($where);
        $result = $this->db->select($key . ', ' . $value)
                           ->order_by($value . ' asc')
                           ->get($table)
                           ->result_array();
        if(!empty($result)){
            foreach($result as $row){
                $return[$row[$key]] = $row[$value];
            }
        }
        return $return;
    }

    /**
     * @param string $table
     * @param int $id
     * @return mixed
     */
    function get_by_id($table, $id){
        return $this->db->where('id', $id)->get($table)->row_array();
    }

    /**
     * @param string $table
     * @param string $key
     * @param string $value
     * @param array $ids
     * @return array
     */
    function get_select_by_ids($table, $key, $value, $ids){
        $return = array();
        if(!empty($ids) && is_array($ids)){
            $data = $this->db->select($key . ', ' . $value)
                             ->where_in('id', $ids)
                             ->get($table)
                             ->result_array();
            if(!empty($data)){
                foreach ($data as $row) {
                    $return[$row[$key]] = $row[$value];
                }
            }
        }
        return $return;
    }

    /**
     * @param string $table
     * @param string $field
     * @param string $id
     */
    function delete_item($table, $field, $id){
        $this->db->where($field, $id)
                 ->delete($table);
    }

    /**
     * @param string $table
     * @param array $ids
     */
    function delete_by_ids($table, $ids){
        $this->db->where_in('id', $ids)
                 ->delete($table);
    }

    /**
     * @param string $table
     * @param array $data
     * @param array $where
     * @return mixed
     */
    function update_item($table, $data, $where){
        $this->db->where($where);
        $this->db->update($table, $data);
        return $this->db->affected_rows();
        //return $this->db->last_query();
    }

    /**
     * @param string $table
     * @param array $data
     * @return mixed
     */
    function insert_item($table, $data){
        $this->db->insert($table, $data);
        return $this->db->insert_id();
        //echo $this->db->last_query() . '<br/>';
    }

    /**
     * @param string $table
     * @param string $value
     * @param array $where
     * @return bool
     */
    function get_one_value($table, $value, $where){
        $data = $this->db->select($value)
                         ->where($where)
                         ->limit(1)
                         ->get($table)
                         ->row_array();
        return (!empty($data[$value])) ? $data[$value] : FALSE;
    }

    /**
     * @param string $table
     * @param array $where
     * @return mixed
     */
    function get_one_row($table, $where){
        return $this->db->where($where)
                        ->limit(1)
                        ->get($table)
                        ->row_array();
    }

    /**
     * @param string $table
     * @param int $num
     * @param string $fields
     * @param string $order_by
     * @param array $where
     * @return mixed
     */
    function get_last_entries($table, $num, $fields = '', $order_by = '', $where = array()){
        if(!empty($fields)) $this->db->select($fields);
        if(!empty($where)) $this->db->where($where);
        if(!empty($order_by)) $this->db->order_by($order_by);
            else $this->db->order_by('id desc');
        $this->db->limit($num);
        return $this->db->get($table)->result_array();
    }

    /**
     * @param string $table
     * @param string $ids_field
     * @param array $ids
     * @param string $fields
     * @param string $order_by
     * @return mixed
     */
    function get_fields_by_ids($table, $ids_field, $ids, $fields = '', $order_by = ''){
        if(!empty($fields)) $this->db->select($fields);
        if(!empty($order_by)) $this->db->order_by($order_by);
        return $this->db->where_in($ids_field, $ids)
                        ->get($table)
                        ->result_array();
    }

    /**
     * @param string $table
     * @param string $fields
     * @param string $like
     * @param array $where
     * @param array $or_like
     * @return mixed
     */
    function get_search($table, $fields, $like, $where, $or_like = array()){
        $this->db->select($fields);
        $this->db->like('title', $like);
        if(!empty($or_like)){
            foreach ($or_like as $item) {
                $this->db->or_like('title', $item);
            }
        }
        $this->db->where($where);
        return $this->db->get($table)
                        ->result_array();
    }

    /**
     * @param string $table
     * @param array $fields
     * @param string $text
     * @param array $where
     * @return mixed
     */
    function get_like_list_where($table, $fields, $text, $where = array()){
        $text = trim($text);
        if(empty($text)) return FALSE;
        
        if(!empty($where)) $this->db->where($where);
        
        if(count($fields) > 1){
            for($i = 0; $i < count($fields); $i++){
                if($i == 0){
                    $this->db->like($fields[$i], $text);
                }
                else{
                    $this->db->or_like($fields[$i], $text);
                }
            }
        }
        elseif(count($fields) == 1){
            $this->db->like($fields[0], $text);
        }
        
        return $this->db->get($table)
                        ->result_array();
    }

    /**
     * @param string $table
     * @param string $field
     * @param array $where
     * @return int
     */
    function get_field_sum($table, $field, $where = array()){
        $this->db->select_sum($field, 'sum');
        if(!empty($where)) $this->db->where($where);
        $sum = $this->db->get($table)
            ->row()->sum;
        return (!empty($sum)) ? $sum : 0;
    }
}
?>
