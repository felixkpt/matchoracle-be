import JSZip from 'jszip';
import useAxios from '@/hooks/useAxios';
import { baseURL } from '@/utils/helpers';
import { useState } from 'react';
import axios from 'axios';

function FileUploader() {
  const { post } = useAxios();
  const [files, setFiles] = useState([]);

  const handleFilesChange = (event) => {
    const fileList = event.target.files;
    const filesArray = Array.from(fileList);
    setFiles(filesArray);
  };

  const uploadFiles = async () => {
    const zip = new JSZip();
    files.forEach(file => {
      zip.file(file.webkitRelativePath, file);
    });

    const zipBlob = await zip.generateAsync({ type: 'blob' });
    const formData = new FormData();
    formData.append('folder', zipBlob, 'folder.zip');

    await axios.post(baseURL('api/dashboard/uploads'), formData);
  };

  return (
    <div>
      <input type="file" webkitdirectory="true" multiple onChange={handleFilesChange} />
      <button onClick={uploadFiles}>Upload</button>
    </div>
  );
}

export default FileUploader;
