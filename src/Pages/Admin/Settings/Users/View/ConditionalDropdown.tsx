import React, { useState, useEffect } from 'react';
import Select from "react-select";
import useAxios from "@/hooks/useAxios";

const ConditionalDropdown = () => {
  const [customers, setCustomers] = useState([]);
  const [facilities, setFacilities] = useState([]);
  const [specialties, setSpecialties] = useState([]);
  const [doctors, setDoctors] = useState([]);
  const [selectedCustomer, setSelectedCustomer] = useState(null);
  const [selectedFacility, setSelectedFacility] = useState(null);
  const [selectedSpecialty, setSelectedSpecialty] = useState(null);
  const [selectedDoctor, setSelectedDoctor] = useState(null);

  const { get } = useAxios();

  useEffect(() => {
    fetchSelectData('admin/customers?all=1', setCustomers);
  }, []);

  const fetchSelectData = (url: string, setDataFunction: (data: []) => any) => {
    get(url).then(data => {
      if (data)
        setDataFunction(data);
    });
  };

  const handleSelectChange = async (job: { uri: string, fn: (data: []) => any }, dependencies: object[]) => {
    let updatedUri = job.uri;

    dependencies.forEach((dependency: any) => {

      const [name, selectedValue] = dependency
      
      updatedUri += `&${name}=${selectedValue?.id || 0}`;

    });

    fetchSelectData(updatedUri, job.fn);

  };

  useEffect(() => {
    if (selectedSpecialty) {
      fetchSelectData(`admin/doctors?all=1&specialty_id=${selectedSpecialty.id}`, setDoctors);
    }
  }, [selectedSpecialty]);


  return (
    <div>
      <div className='form-group mb-2'>
        <Select
          value={selectedCustomer}
          onChange={(newValue) => {
            setSelectedCustomer(newValue)
            handleSelectChange(
              { uri: 'admin/facilities?all=1', fn: setFacilities },
              [
                ['customer_id', newValue, setFacilities],
                ['another_id', newValue, setDoctors],
              ],
            )
          }
          }
          options={customers}
          getOptionValue={(option:any) => option && `${option?.id}`}
          getOptionLabel={(option:any) => option && `${option?.name}`}
        />
      </div>
      <div className='form-group mb-2'>
        <Select
          value={selectedFacility}
          onChange={(newValue) => {
            setSelectedFacility(newValue)
            handleSelectChange(
              { uri: 'admin/specialties?all=1', fn: setSpecialties },
              [
                ['customer_id', selectedCustomer],
                ['facility_id', newValue],
              ],
            )
          }
          }
          options={facilities}
          getOptionValue={(option:any) => option && `${option?.id}`}
          getOptionLabel={(option:any) => option && `${option?.name}`}
        />
      </div>
      <div className='form-group mb-2'>
        <Select
          value={selectedSpecialty}
          onChange={(newValue) => {
            setSelectedSpecialty(newValue)
            handleSelectChange(
              { uri: 'admin/doctors?all=1', fn: setDoctors },
              [
                ['customer_id', selectedCustomer],
                ['facility_id', selectedFacility],
                ['specialty_id', newValue],
              ],
            )
          }
          }
          options={specialties}
          getOptionValue={(option:any) => option && `${option?.id}`}
          getOptionLabel={(option:any) => option && `${option?.name}`}
        />
      </div>
    </div>
  );
};

export default ConditionalDropdown;