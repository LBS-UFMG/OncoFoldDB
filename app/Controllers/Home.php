<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        # BUSCAR DADOS ESTATÍSTICOS
		$data = array();

        $url = "./data/pdb/total_contacts.txt";

        $data['h1'] = "48";
        $data['h2'] = "205";
        $data['h3'] = "199";
        $data['h4'] = "404";

        $data['update'] = 'Apr 2025';

        if (file_exists($url)) {
            # se houver um arquivo de configuração, atualize os valores
            $file_handle = fopen($url, 'r');
            if($file_handle) {
                $current_line = 1;
                while (($line = fgets($file_handle)) !== false) {
                    switch($current_line){
                        case 1: $data['h2'] = number_format((int)$line, 0, '', ','); $current_line++; break;
                        case 2: $data['h3'] = number_format($line, 0, '', ','); $current_line++; break;
                        case 3: $data['h1'] = number_format($line, 0, '', ','); $current_line++; break;
                        case 4: $data['h4'] = number_format($line, 0, '', ','); $current_line++; break;
                        case 5: $data['update'] = $line; $current_line++; break;
                    }
                }
                fclose($file_handle);
            } else {
                echo "Error.";
            }
        }

        return view('home', $data);
    }

    public function documentation(): string
    {
        return view('documentation');
    }

    public function download(): string
    {
        return view('download');
    }

    public function blast(): string
    {
        return view('blast');
    }

    public function explore(): string
    {
        return view('explore');
    }

    private function getInfo($id): Array 
    {
        $url = "./data/mutants/$id";

        if (!file_exists($url)) {
            return ["File not exist."];
        }

        $file_handle = fopen("./data/list.csv", 'r');
        $lines = "";
        if($file_handle) {
            while (($line = fgets($file_handle)) !== false) {
                $linex = explode("\t",$line);
                if($linex[0] == $id){
                    $lines = $line;
                }
            }
            fclose($file_handle);
        } else {
            echo "Error.";
        }
        
        $info = explode("\t", $lines);
        return $info;
    }


    private function getContacts($id): Array 
    {
        $contacts = [];
        $first_letter = substr($id, 0, 1);

        # contacts
        $url = "./data/pdb/$first_letter/$id/$id"."_contacts.csv";
        if (!file_exists($url)) {
            return ["File not exist."];
        }
        $file_handle = fopen($url, 'r');
        if ($file_handle) {
            while (($line = fgets($file_handle)) !== false) {
                array_push($contacts,$line);
            }
            fclose($file_handle);
        } else {
            echo "Error.";
        }
        
        return $contacts;
    }

    public function entry($id): string
    {
        $data = [];
        $data['id'] = $id;

        // código inexistente
        if((strlen($id) < 2)or(strlen($id) > 7)){
            return view('404', $data);
        }

        // pega informações básicas
        $data['info'] = $this->getInfo($id);
        if($data['info'][0] == "File not exist."){
            return view('404', $data);
        }
        $data['total_results'] = 1;
        // pega informações de contatos

        $data['gene'] = $data['info'][0];
        $data['total_drivers'] = $data['info'][1];
        $data['total_nondrivers'] = $data['info'][2];
        $data['drivers'] = explode(', ', $data['info'][3]);
        $data['nondrivers'] = explode(', ', $data['info'][4]);

        // $data['contacts'] = explode(',',$data['info'][0][3].','.$data['info'][0][3]);

        return view('entry', $data);
    }

}
