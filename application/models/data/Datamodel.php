<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Datamodel extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }


    public function get_data_ibu(){  
        $ibus = $this->db->query("SELECT data_identitas_ibu.name as nama, data_identitas_ibu.unique_id as uniqueid, data_identitas_ibu.dusun as dusunasal,data_identitas_ibu.telp as telpon, data_identitas_ibu.*, data_rencana_persalinan.*, data_status_persalinan.*, data_transportasi.* FROM data_identitas_ibu LEFT JOIN data_rencana_persalinan ON data_identitas_ibu.unique_id=data_rencana_persalinan.id_ibu LEFT JOIN data_status_persalinan ON data_identitas_ibu.unique_id=data_status_persalinan.id_ibu LEFT JOIN data_transportasi ON data_rencana_persalinan.id_trans=data_transportasi.unique_id LEFT JOIN data_close_ibu ON data_identitas_ibu.unique_id=data_close_ibu.unique_id WHERE data_close_ibu.alasan IS NULL")->result();
        $ids = [];
        $res = [];
        foreach ($ibus as $ibu) {
            $ids[] = $ibu->uniqueid;
            $res[$ibu->uniqueid] = $ibu;
        }
        $ibus_edit = $this->db->query("SELECT * FROM (SELECT * FROM data_identitas_ibu_edit WHERE id IN (SELECT MAX(id) FROM data_identitas_ibu_edit GROUP BY unique_id)) edit WHERE unique_id IN ('".implode("','", $ids)."')")->result();
        foreach ($ibus_edit as $edt) {
            if (array_key_exists($edt->unique_id, $res)) {
                foreach ($edt as $key => $value) {
                    $res[$edt->unique_id]->$key = $value;
                }
            }
        }
        return $res;

        return $this->db->query("SELECT data_identitas_ibu.name as nama,data_identitas_ibu.dusun as dusunasal,data_identitas_ibu.telp as telpon, data_identitas_ibu.*, data_rencana_persalinan.*, data_status_persalinan.*, data_transportasi.* FROM data_identitas_ibu LEFT JOIN data_rencana_persalinan ON data_identitas_ibu.unique_id=data_rencana_persalinan.id_ibu LEFT JOIN data_status_persalinan ON data_identitas_ibu.unique_id=data_status_persalinan.id_ibu LEFT JOIN data_transportasi ON data_rencana_persalinan.id_trans=data_transportasi.unique_id LEFT JOIN (SELECT * FROM data_identitas_ibu_edit WHERE id IN (SELECT MAX(id) FROM data_identitas_ibu_edit GROUP BY unique_id)) edit ON data_identitas_ibu.unique_id=edit.unique_id")->result();
    }

    public function get_data_transportasi(){  
        $trans = $this->db->query("SELECT * FROM data_transportasi")->result();
        $ids = [];
        $res = [];
        foreach ($trans as $tran) {
            $ids[] = $tran->unique_id;
            $res[$tran->unique_id] = $tran;
        }
        $trans_edit = $this->db->query("SELECT * FROM (SELECT * FROM data_transportasi_edit WHERE id IN (SELECT MAX(id) FROM data_transportasi_edit GROUP BY unique_id)) edit WHERE unique_id IN ('".implode("','", $ids)."')")->result();
        foreach ($trans_edit as $edt) {
            if (array_key_exists($edt->unique_id, $res)) {
                foreach ($edt as $key => $value) {
                    $res[$edt->unique_id]->$key = $value;
                }
            }
        }
        return $res;
    }

    public function get_data_bank_darah(){  
        $banks = $this->db->query("SELECT * FROM data_bank_darah")->result();
        $ids = [];
        $res = [];
        foreach ($banks as $bank) {
            $ids[] = $bank->unique_id;
            $res[$bank->unique_id] = $bank;
        }
        $banks_edit = $this->db->query("SELECT * FROM (SELECT * FROM data_bank_darah_edit WHERE id IN (SELECT MAX(id) FROM data_bank_darah_edit GROUP BY unique_id)) edit WHERE unique_id IN ('".implode("','", $ids)."')")->result();
        foreach ($banks_edit as $edt) {
            if (array_key_exists($edt->unique_id, $res)) {
                foreach ($edt as $key => $value) {
                    $res[$edt->unique_id]->$key = $value;
                }
            }
        }
        return $res;
    }

    public function getLoginInfo($username) {
        $this->db->select('id,username,email,created_on,last_login,active,first_name,last_name,company,phone');
        $this->db->where('username', $username);
        $user = $this->db->get('users')->row_array();

        $this->db->select('*');
        $this->db->where('user_id', $user['id']);
        $user_map = $this->db->get('user_map')->row_array();

        $this->db->select('*,name');
        $this->db->where('user_id', $user['id']);
        $this->db->join('groups', 'users_groups.group_id = groups.id');
        $users_groups = $this->db->get('users_groups')->row_array();
        $user['groups'] = $users_groups['name'];

        $res['user'] = $user;

        $this->db->select('location.*,location_tag.name as tag_name, description');
        $this->db->join('location_tag', 'location.location_tag_id = location_tag.location_tag_id');
        $this->db->where('location_id', $user_map['location_id']);
        $loc = $this->db->get('location')->row_array();
        $res['user_location'] = $loc;

        // $locs = [];
        // $locs[] = $loc;
        // while($loc['parent_location']!=NULL){
        //     $loc = $this->getParentLocation($loc);
        //     $locs[] = $loc;
        // }
        // $locs = array_reverse($locs);
        // $locs = $this->getChildLocations($locs,$res['user_location']);
        

        // $res['locations_tree'] = $locs;

        return $res;
    }

    private function getParentLocation($loc){
        $this->db->select('*');
        $this->db->where('location_id', $loc['parent_location']);
        $loc = $this->db->get('location')->row_array();
        return $loc;
    }

    private function getChildLocations($locs,$loc){
        $this->db->select('*');
        $this->db->where('parent_location', $loc['location_id']);
        $result = $this->db->get('location')->result_array();
        foreach ($result as $child) {
            $locs[] = $child;
            $locs = $this->getChildLocations($locs,$child);
        }
        return $locs;
    }

    public function getChildLocationsById($locId){
        $this->db->select('*');
        $this->db->where('parent_location', $locId);
        $result = $this->db->get('location')->result_array();
        $locs = [];
        foreach ($result as $child) {
            $locs[] = $child;
        }
        return $locs;
    }

    public function getLocations($locations_tree){
        $this->db->select('*');
        $tags = $this->db->get('location_tag')->result_array();
        $loc_tags = [];
        foreach ($tags as $tag) {
            $loc_tags[$tag['location_tag_id']] = $tag;
        }
        $res = [];
        foreach ($locations_tree as $loc) {
            $res[$loc_tags[$loc['location_tag_id']]['name']][] = $loc;
        }
        return $res;
    }
}
