import JSZip from 'jszip';
import { baseURL } from '@/utils/helpers';
import { useState } from 'react';
import axios from 'axios';

function FileUploader() {
  const [files, setFiles] = useState<File[]>([]);

  const handleFilesChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const fileList = event.target.files;
    if (fileList) {
      const filesArray = Array.from(fileList);
      setFiles(filesArray);
    }
  };

  const uploadFiles = async () => {
    const zip = new JSZip();
    files.forEach(file => {
      zip.file(file.webkitRelativePath || file.name, file);
    });

    const zipBlob = await zip.generateAsync({ type: 'blob' });
    const formData = new FormData();
    formData.append('folder', zipBlob, 'folder.zip');

    await axios.post(baseURL('api/dashboard/uploads'), formData);
  };

  return (
    <div>
      <input
        type="file"
        multiple
        onChange={handleFilesChange}
        // @ts-expect-error unhandled
        webkitdirectory="true"
      />
      <button onClick={uploadFiles}>Upload</button>
    </div>
  );
}

export default FileUploader;
