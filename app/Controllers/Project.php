<?php

namespace App\Controllers;

class Project extends BaseController
{

    public function id($id = 'null'){
		# PÁGINA ENTRY

		$id = substr($id, 0, 6);

		# ********************* Search project *********************
		# Read directory
		chdir('data/projects');
		$projects = glob("{*}", GLOB_BRACE);
		$project_exists = False;

		# Is the id unique? If not, create a new!
		for($i = 0; $i < (count($projects)); $i++){
			if($projects[$i] == $id){
				$project_exists = True;
			}
		}

		# Project does not exist
		if(!$project_exists){
			$view = 'project_fail';
			$data = array();
			return view('404', $data);
		}
		else{

            # ********************* Loading data *********************
            # Mutations
			$data_folder = getcwd(); // este código identifica a pasta local
			$raiz = str_replace("/public/data/projects", "",$data_folder);

			if (!file_exists($data_folder.'/'.$id.'/contacts.csv')) {
				dd("Failed to run the project. Please try again by submitting a file in PDB format or contact the system administrator.");
			}

			$contacts_file = fopen($data_folder.'/'.$id.'/contacts.csv','r');
            $contacts = array();
            $total_results = 0;
            while (($line = fgets($contacts_file, 4096)) !== false) {
                array_push($contacts, $line);
                $total_results++;
            }
            fclose($contacts_file);
            
            $view = 'project';

            // Load template
            $data['contacts'] = $contacts;
            $data['id'] = $id;
            $data['total_results'] = $total_results-3;

			if (file_exists($data_folder.'/'.$id.'/data.pdb')){
				$data['extensao'] = 'pdb';
			}
			else{
				$data['extensao'] = 'cif';
			}

            return view($view, $data);
		}
	}



    public function create(){

		# ********************* Create new ID *********************
		$id = $this->generateRandomString(6);
		
		# Read directory
		if (file_exists('../public/data/projects')) {
			chdir('../public/data/projects');
		}
		else{
			chdir('../data/projects');
		}
		
		$arquivos = glob("{*}", GLOB_BRACE);

		# Is the id unique? If not, create a new!
		for($i = 0; $i < (count($arquivos)); $i++){
			if($arquivos[$i] == $id){
				$id = $this->generateRandomString(6);
				$i = 0;
			}
		}

		# Create project folder 
		mkdir("../../../public/data/projects/$id");
		chmod("../../../public/data/projects/$id", 0777);


		# ********************* Receiving post data *********************

		$pdb = $this->request->getPost("pdb");
		$data_folder = getcwd();
		$raiz = str_replace("/public/data/projects", "",$data_folder);

		# Saving project data
		// $project = fopen($data_folder.'/'.$id.'/data.pdb','w');
		// fwrite($project,$pdb); 
		// fclose($project);


        $file = $this->request->getFile('pdbfile');
		if(!empty($file)){
			$extensao = strtolower(substr($file->getName(), -3, 3));

			if(($extensao=='pdb')or($extensao=='cif')){

				$tamanho = $file->getSize();

				if($tamanho > 10485760){
					dd("Error! Max file size: 10MB.");
				}
				else{
					// Submit file
					if($extensao=='pdb'){
						$file->move("$data_folder/$id", 'data.pdb');
					}
					else{
						$file->move("$data_folder/$id", 'data.cif');
					}
				}
			}
			else{
				dd("Error! Format not allowed. Submit a PDB or a CIF file.");
			}
		}
		else{
			dd("Error: Empty file.");
		}
		

        # Security
        #chmod("../../public/data/$id", 0644);
				
		echo "<div class='bg-info small text-center'><div class='container-fluid px-5'><strong>COCaDA CLI status: </strong>"; // message style box
		# START cocada PIPELINE *******************************************
		$interpretador = "/home/liase/miniconda3/bin/python"; 
		#$interpretador = "python3.8";
		#$interpretador = "/usr/bin/python3.6"; 
		#$interpretador = "/bin/python3";
		#$versao = 'cocada_alfa'; # stable
		$versao = 'COCaDA_web';
		chmod("../../../public/data/projects/$id", 0777);

		#echo "$interpretador $raiz/app/ThirdParty/$versao/main.py -f $data_folder/$id/data.$extensao -o $data_folder/$id";
		system("$interpretador $raiz/app/ThirdParty/$versao/main.py -f $data_folder/$id/data.$extensao -o $data_folder/$id  2>&1",$error_log);
		// 	echo $error_log;

		# renomeia o arquivo com a lista de contatos
		#system("mv $data_folder/$id/*.txt $data_folder/$id/contacts.csv");
		// dd("python3 $raiz/app/ThirdParty/COCaDA/main.py -f $data_folder/$id/data.pdb -o $data_folder/$id");

		$data = array();
		$data['id'] = $id;
		echo '</div></div>'; // end message style box
		chmod("../../../public/data/projects/$id", 0755);

		return view('running', $data);

	}

    private function generateRandomString($size){

		$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$randomString = '';
		
		for($i = 0; $i < $size; $i = $i+1){
			$randomString .= $chars[mt_rand(0,35)];
		}

		return $randomString;

	}
    
}
