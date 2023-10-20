import FormatDate from "@/utils/FormatDate";
import DatePicker from "react-datepicker";

type Props = {
  title: string;
  startDate: any
  setStartDate: any
};

const MatchesPageHeader = ({ title, startDate, setStartDate }: Props) => {

  return (
    <div className='header-title shadow-sm p-2 rounded mb-3 d-flex justify-content-between'>
      <h3 className='heading'>{title}</h3>
      <div>
        {
          typeof setStartDate === 'function' &&
          <DatePicker selected={new Date(startDate)} onChange={(date: Date) => setStartDate(FormatDate.YYYYMMDD(date))} />
        }
      </div>
    </div>
  )
}

export default MatchesPageHeader