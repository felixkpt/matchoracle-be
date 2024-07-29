import { ActionsType, ModalSizeType } from '@/interfaces/UncategorizedInterfaces';
import { publish } from '@/utils/events';
import { useNavigate } from 'react-router-dom';

type Props = {
  modelDetails: any
  tableData: any
  actions: ActionsType
  listSources: any
  exclude?: string[]
  modalSize?: ModalSizeType
  customModalId?: string
  isSingle?: boolean
}

const useAutoAction = ({ modelDetails, tableData, actions, listSources, exclude, modalSize, customModalId, isSingle }: Props) => {

  const navigate = useNavigate()

  const handleView = (event: any) => {
    event.preventDefault();

    const target = event.target instanceof HTMLElement ? event.target : null;

    if (!target) return;

    const id = target.getAttribute('data-id');
    const action = target.getAttribute('data-action') || target.getAttribute('href');

    if (!id || !action) return;

    if (actions?.view?.actionMode === 'navigation') return navigate(action)

    const record = isSingle ? tableData : tableData.data.find((item: any) => item.id == id);


    publish('prepareView', {
      modelDetails,
      record,
      action,
      modalSize,
      customModalId,
      exclude
    });
  };

  const handleEdit = (event: any) => {
    event.preventDefault();

    const target = event.target instanceof HTMLElement ? event.target : null;

    if (!target) return;

    const id = target.getAttribute('data-id');
    const action = (target.getAttribute('data-action') || target.getAttribute('href'))?.replace(/\/edit/g, '');

    if (!id || !action) return;

    if (actions?.edit?.actionMode === 'navigation') return navigate(action)

    const record = isSingle ? tableData : tableData.data.find((item: any) => item.id == id);


    publish('prepareEdit', {
      modelDetails,
      record,
      action,
      listSources,
      modalSize,
      customModalId
    });
  };

  const handleUpdateStatus = (event: any) => {
    event.preventDefault();

    const target = event.target instanceof HTMLElement ? event.target : null;

    if (!target) return;

    const id = target.getAttribute('data-id');
    const action = target.getAttribute('href');

    if (!id || !action) return;

    if (actions?.statusUpdate?.actionMode === 'navigation') return navigate(action)

    const record = isSingle ? tableData : tableData.data.find((item: any) => item.id == id);

    publish('prepareStatusUpdate', {
      modelDetails,
      record,
      action,
      modalSize
    });
  };

  return {
    handleView,
    handleEdit,
    handleUpdateStatus,
  };
};

export default useAutoAction;
