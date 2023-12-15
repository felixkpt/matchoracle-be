import FormatDate from "@/utils/FormatDate";
import DatePicker from "react-datepicker";

type Props = {
  title: string;
  fromToDates: any
  setFromToDates: any
};

const MatchesPageHeader = ({ title, fromToDates, setFromToDates }: Props) => {

  return (
    <div className='header-title shadow-sm p-2 rounded mb-3 d-flex justify-content-between'>
      <h3 className='heading'>{title}</h3>
      <div>
        {
          typeof setFromToDates === 'function' &&
          <DatePicker selected={new Date(fromToDates)} onChange={(date: Date) => setFromToDates(FormatDate.YYYYMMDD(date))} />
        }
      </div>
    </div>
  )
}

export default MatchesPageHeader