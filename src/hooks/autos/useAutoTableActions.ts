import { useState } from "react";

export function useAutoTableActions() {
  const [row, setRow] = useState(null);
  const [viewModalOpen, setViewModalOpen] = useState(undefined);
  const [editModalOpen, setEditModalOpen] = useState(false);
  const [reload, setReload] = useState(0);
  const [openModal, setOpenModal] = useState(undefined);

  const handleView = (row) => {
    setRow(row);
    setViewModalOpen((prev) => (prev === undefined ? true : !prev));
  };

  const handleEdit = (row) => {
    setRow(row);
    setEditModalOpen(true);
  };

  const handleDelete = (row) => {
    setRow(row);
    setOpenModal("pop-up");
  };

  const response = (response) => {
    if (response.type === "success") {
      setEditModalOpen(false);
      setReload((prev) => prev + 1);
    } else if (response.type === "cancelled") {
      setEditModalOpen(false);
    }
  };

  const deleteResponse = (response) => {
    console.log(response);
    setOpenModal(undefined);
    setReload((prev) => prev + 1);
  };

  return {
    row,
    viewModalOpen,
    editModalOpen,
    reload,
    openModal,
    handleView,
    handleEdit,
    handleDelete,
    response,
    deleteResponse,
  };
}
