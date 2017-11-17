<?php
include_once("./modules/shop/shop.helper.php");

class statsModule extends shopModuleHelper {
	function __construct()
	{
		parent::__construct();

		$this->load->library("categories");
                $this->load->library('stats');
	}

        public function products_all(){
            $this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);
            
            $data['num_res'] = $this->ci->stats->get_items('title', 1);
            $pagination = $this->ci->fb->pagination_init($data['num_res'], 20, current_url_query(array("pg" => NULL)), "pg");
            $data['results'] = $this->ci->stats->get_items('id,title,code,views', 0, $pagination->per_page, $pagination->cur_page);
            $rows = array();
            if(!empty($data['results'])){
                foreach ($data['results'] as $row){
                    $rows[] = array($row['id'], $row['title'], $row['code'], $row['views']);
                }
            }
            
            $this->ci->fb->add("table",array(
				"id"=>"products",
				"parent"=>"table",
				"head"=>array(
                                        "ID",
					"Наименование",
                                        "Ариткул",
                                        "Просмотры",
				),
				"rows"=>$rows,
				"rows_num"=>$data['num_res'],
				"pagination"=>$pagination->create_links()
			));
            
            // gCharts
            $this->load->library('gcharts');
            $this->ci->gcharts->load('ColumnChart');
            $data['display_charts'] = 1; // enable/disable chart
            $data['chart_name'] = 'Views';
            $data['chart_type'] = 'ColumnChart';
            $months = array(
                1 => "Январь", 2 => "Февраль", 3 => "Март", 4 => "Апрель", 
                5 => "Май", 6 => "Июнь", 7 => "Июль", 8 => "Август", 9 => "Сентябрь", 
                10 => "Октябрь", 11 => "Ноябрь", 12 => "Декабрь"
            );
            $data_type = 'months'; // months or years
            
            $cart_res = $this->ci->stats->get_chart_data_by($data_type);
            $chart_data = array('');
            $this->ci->gcharts->DataTable($data['chart_name'])
                          ->addColumn('string', 'Classroom', 'class');
            
            if (!empty($cart_res)) {
                foreach ($cart_res as $key => $res) {
                    $res_title = ($data_type == 'months') ? $months[$res['date']] : $res['date'];
//                    $res['cnt'] = ($data_type == 'years' && $res['date'] == '2015') ? 267+$res['cnt'] : $res['cnt'];
                    $this->ci->gcharts->DataTable($data['chart_name'])
                            ->addColumn('number', $res_title, 'date' . ++$key);
                    $chart_data[] = $res['cnt'];
                }
            }
            
            $this->ci->gcharts->DataTable($data['chart_name'])
                        ->addRow($chart_data);
            
            $chart_config = array(
                'title' => 'Количество просмотров, по месяцам',
                'legend' => $this->ci->gcharts->legend()->position('none'),
            );

            $this->ci->gcharts->ColumnChart($data['chart_name'])->setConfig($chart_config);
            //
            
            $data['render'] = $this->ci->fb->render("table");
            $this->ci->load->adminView("stats/products_all", $data);
        }
        
        public function products_by(){
            $this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);
            
            $period = $this->input->get('by');
            $periods = array('today', 'week', 'month');
            $period = (in_array($period, $periods)) ? $period : 'month';
            $data['num_res'] = $this->ci->stats->get_items_by('title', 1, $period);
            $pagination = $this->ci->fb->pagination_init($data['num_res'], 20, current_url_query(array("pg" => NULL)), "pg");
            $data['results'] = $this->ci->stats->get_items_by('id,title,code,views', 0, $period, $pagination->per_page, $pagination->cur_page);
            $rows = array();
            if(!empty($data['results'])){
                foreach ($data['results'] as $row){
                    $rows[] = array($row['id'], $row['title'], $row['code'], $row['views']);
                }
            }
            
            $this->ci->fb->add("table",array(
				"id"=>"products",
				"parent"=>"table",
				"head"=>array(
                                        "ID",
					"Наименование",
                                        "Артикул",
                                        "Просмотры",
				),
				"rows"=>$rows,
				"rows_num"=>$data['num_res'],
				"pagination"=>$pagination->create_links()
			));
            
            // gCharts
            if (in_array($period, array('today', 'week', 'month'))) {
                $this->load->library('gcharts');
                $this->ci->gcharts->load('LineChart');
                $data['display_charts'] = 1; // enable/disable chart
                $data['chart_name'] = 'Views';
                $data['chart_type'] = 'LineChart';
                $chart_column_type = ($period === 'today') ? 'number' : 'date';

                $this->ci->gcharts->DataTable($data['chart_name'])
                            ->addColumn($chart_column_type, 'Dates', 'dates')
                            ->addColumn('number', 'Просмотры', 'views');
                
                $chart_res = ($period === 'today') ? $this->ci->stats->get_chart_by_day() : $this->ci->stats->get_chart_data($period);
                
                if(!empty($chart_res)){
                    foreach ($chart_res as $res) {
                        $date_ex = explode('-', $res['date']);
                        $res_date = ($period === 'today') ? $res['date'] : new jsDate($date_ex[0], $date_ex[1]-1, $date_ex[2]);
                        $chart_data = array(
                            $res_date,
                            $res['cnt'],
                        );
                        $this->ci->gcharts->DataTable($data['chart_name'])->addRow($chart_data);
                    }
                }

                $chart_config = array(
                    'title' => 'Просмотры',
                    'legend' => $this->ci->gcharts->legend()->position('none'),
                    'pointSize' => 3,
                );

                $this->ci->gcharts->LineChart($data['chart_name'])->setConfig($chart_config);
            }
            //
            
            $data['render'] = $this->ci->fb->render("table");
            $this->ci->load->adminView("stats/products_all", $data);
        }
        
        public function summary(){
            $this->plugin_trigger("onMethodStart",__FILE__,__CLASS__,__FUNCTION__,__LINE__);
            
            $data['summary'] = $this->ci->stats->get_summary();
            $this->ci->load->adminView("stats/summary", $data);
        }
        
}