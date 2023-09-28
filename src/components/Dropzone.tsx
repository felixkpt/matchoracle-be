import { useDropzone, FileRejection, DropEvent } from 'react-dropzone';
import "@/styles/dropzone.scss";
import React, { useCallback, useState, useMemo } from "react";

interface Props<T extends Blob | MediaSource> {
  files: T[];
  setFiles: React.Dispatch<React.SetStateAction<T[]>>;
  maxFiles?: number;
  fileType?: string;
  id?: string
}

export default function Dropzone<T extends Blob | MediaSource>({ files, setFiles, maxFiles = 1, fileType = 'file', id }: Props<T>) {
  const [selectedImages, setSelectedImages] = useState<T[]>(files);
  const [isMultiple] = useState(maxFiles > 1);

  const onDrop = useCallback((acceptedFiles: T[], fileRejections: FileRejection[], event: DropEvent) => {
    // Ensure that we don't exceed the maxFiles limit
    const newFiles = [...files, ...acceptedFiles];
    const trimmedFiles = newFiles.slice(-maxFiles);

    setFiles(trimmedFiles);
    setSelectedImages(trimmedFiles);
  }, [files, maxFiles, setFiles]);

  const {
    getRootProps,
    getInputProps,
    isDragActive,
    isDragAccept,
    isDragReject
  } = useDropzone({
    onDrop: onDrop as unknown as (acceptedFiles: File[], fileRejections: FileRejection[], event: DropEvent) => void,
    maxFiles: maxFiles || 1,
    multiple: isMultiple ? true : false
  });

  const style = useMemo(
    () => ({
      ...(isDragAccept ? { borderColor: "#00e676" } : {}),
      ...(isDragReject ? { borderColor: "#ff1744" } : {})
    }),
    [isDragAccept, isDragReject]
  );

  return (
    <div className='dropzone-container' id={id || 'DropzoneSection'}>
      <div className={'dropzone'} {...getRootProps({ style })}>
        <input {...getInputProps()} />
        {
          isDragActive ?
            <p>Drop {fileType}{isMultiple ? '(s)' : ''} here ...</p>
            :
            <p>Drag and drop {fileType}{isMultiple ? '(s)' : ''} here, or click to select {fileType}{isMultiple ? '(s)' : ''}</p>
        }
      </div>
      <div className={'images'}>
        {selectedImages.length > 0 &&
          selectedImages.map((image, index) => (
            <img src={`${URL.createObjectURL(image)}`} key={index} alt="" />
          ))}
      </div>
    </div>
  );
}
