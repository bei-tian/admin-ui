<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// ------------------------------------------------------------------------

/**
 * 扩展CI的CI_Model类
 *
 * @package        CodeIgniter
 * @subpackage    models
 * @category    MY_Model
 * @author        South
 */
class MY_model extends CI_model {
    private $table;


    public function setTable($table) {
        $this->table = $table;
        return $this;
    }

    function getData($config) {
        $query = $this->db
            ->select($config['field'])
            ->from($this->table);
        foreach ($config['where'] as $key=>$item) {
            if(strpos($key,' in')) {
                $query->where_in(current(explode(' ',$key)),$item);
                unset($config['where'][$key]);
            }
        }
        foreach ($config['join'] as $item) {
            $query->join($item[0],$item[1],$item[2]);
        }
        $result = $query
            ->where($config['where'])
            ->order_by($config['order'])
            ->limit($config['limit'])
            ->get()
            ->result_array();

        return $result;
    }

    //获取某张表的列表数据
    function getList($where=[],$field='*',$order='',$limit=null){
        $query = $this->db
            ->select($field)
            ->from($this->table);

        foreach ($where as $key=>$item) {
            if(strpos($key,' in')) {
                $query->where_in(current(explode(' ',$key)),$item);
                unset($where[$key]);
            }
        }

        $result = $query
            ->where($where)
            ->order_by($order)
            ->limit($limit)
            ->get()
            ->result_array();

        return $result;
    }

    //获取一行数据
    function getOne($where=[],$field='*',$order=''){
        $result=$this->getList($where,$field,$order,1);
        return $result[0];
    }

    //快速根据id得到某张表的某个值或某些值
    function getValue($id,$field = '',$idName='id'){
        $result = $this->getOne([$idName=>$id],$field);

        if(count($result) == 1) {
            return $result[$field];
        } else {
            return $result;
        }
    }

    //根据id查找信息
    function find($id){
        $result= $this->getOne(['id'=>$id]);
        return $result;
    }

    //查找记录条数
    function count($where){
        $result= $this->db->where($where)->count_all_results($this->table,true);
        return $result;
    }


    //添加新数据
    function add($data){

        $this->db->insert($this->table,$data);
        return $this->db->insert_id();
    }

    //替换插入
    function replace($data){
        $this->db->replace($this->table,$data);
        return $this->db->insert_id();
    }

    //修改数据
    function save($data,$where=[]){
        if(!empty($where)){
            return $this->db->where($where)->update($this->table, $data);
        }else{
            return false;
        }
    }

    //删除数据
    function del($where=[]){
        if(!empty($where)){
            return $this->db->where($where)->delete($this->table);
        }else{
            return false;
        }
    }
}
