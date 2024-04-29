
'use client';

import { HiOutlineExclamationCircle } from "react-icons/hi";


interface ModalProps {
  columns?: { label: string; key: string }[];
  row: any;
  baseUri: string;
  method?: 'POST' | 'DELETE' | 'post' | 'delete';
  response: (data: ResponseData) => ResponseData | void;
  openModal: string | undefined;
  onClose: (val: any) => void;
  size?: string
}

import { Button, Modal } from 'flowbite-react';

const DeleteModal: React.FC<ModalProps> = ({ row, baseUri, response, openModal, onClose, size }) => {

  let uri = baseUri
  if (row.id)
    uri = baseUri + '/' + row.id

  function makeRequest() {
    router.delete(uri, {
      onSuccess: () => response({ type: 'success', message: 'Process was completed', data: null })
    })
  }

  function handleClose() {
    response({ type: 'cancelled', message: 'Process was cancelled', data: null })
  }

  return (
    <>
      <Modal show={openModal === 'pop-up'} size={size} popup onClose={() => handleClose()}>
        <Modal.Header />
        <Modal.Body>
          <div className="text-center">
            <HiOutlineExclamationCircle className="mx-auto mb-4 h-14 w-14 text-gray-400 dark:text-gray-200" />
            <h3 className="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">
              Are you sure you want to delete this product?
            </h3>
            <div className="flex justify-center gap-4">
              <Button color="failure" onClick={() => makeRequest()}>
                Yes, I'm sure
              </Button>
              <Button color="gray" onClick={() => handleClose()}>
                No, cancel
              </Button>
            </div>
          </div>
        </Modal.Body>
      </Modal>
    </>
  )
}

export default DeleteModal