"""
Author: Rafael Lemos - rafaellemos42@gmail.com
Date: 12/08/2024

License: MIT License
"""

import os
from timeit import default_timer as timer

import parser
import argparser
import contacts

def main():
    """
    Main function for the script.

    This function parses command-line arguments, sets up the environment based on the specified mode,
    and runs the appropriate processing function (single-core or multi-core) on the input files.
    It also manages core affinity, output folder creation, and timing of the entire process.
    """
    global_time_start = timer()
    
    file_list, output = argparser.cl_parse()
               
    if output:
        if not os.path.exists(output):
            os.makedirs(output)
    else:
        output = None
    
    for file in file_list:
        try:
            result = process_file(file)
            process_result(result, output)
        except Exception as e:
            print(f"Error: {e}")


def process_file(file_path):
    """
    Processes a single file for contact detection.

    Args:
        file_path (str): Path to the file to be processed.

    Returns:
        tuple: A tuple containing the processed Protein object, the list of detected contacts, and the processing time.
        None: If the file cannot be processed or an error occurs.

    This function parses the PDB or mmCIF file, detects contacts, and returns the results. 
    If an error occurs during processing, it logs the error and returns None.
    """
    start_time = timer()

    try:
        parsed_data = parser.parse_pdb(file_path) if file_path.endswith(".pdb") else parser.parse_cif(file_path)
            
        if parsed_data.true_count() > 25000:  # Skip very large proteins (customizable)
            print(f"Skipping ID '{parsed_data.id}'. Size: {parsed_data.true_count()} residues")     
            return None

        contacts_list = contacts.contact_detection(parsed_data)
        process_time = timer() - start_time
        return parsed_data, contacts_list, process_time

    except Exception as e:
        print(f"Error processing {file_path}: {e}")
        return None


def process_result(result, output):
    """
    Handles the result of processing a file.

    Args:
        result (tuple): A tuple containing the processed Protein object, contacts list, and processing time.
        output (str): The directory where output files will be saved.
    """
    if result:
        protein, contacts_list, process_time = result
        
        if output:
            output_folder = f"{output}/"
            
            number_contacts = contacts.count_contacts(contacts_list)
            number_contacts = ','.join(map(str, number_contacts))
            
            if not os.path.exists(output_folder):
                os.makedirs(output_folder)
            
            with open(f"{output_folder}/contacts.csv","w") as f:
                f.write(contacts.show_contacts(contacts_list))
            
            with open(f"{output_folder}/info.csv","w") as f:
                f.write(f"{protein.id},{protein.title},{protein.true_count()},{len(contacts_list)},{number_contacts}")


            #with open(f"{output}/list.csv","a") as f:
            #    f.write(f"{protein.id},{protein.title},{protein.true_count()},{len(contacts_list)}\n")


if __name__ == "__main__":
    main()
