import { useEffect, useState } from "react";
import Details from "./Tabs/Details";
import Competitions from "./Tabs/Competitions";
import AutoTabs from "@/components/Autos/AutoTabs";
import { useParams } from "react-router-dom";
import { CountryInterface } from "@/interfaces/FootballInterface";
import useAxios from "@/hooks/useAxios";
import CountryHeader from "./Includes/CountryHeader";
import Loader from "@/components/Loader";

const Index: React.FC = () => {
  const { id } = useParams()
  const { get, loading } = useAxios()
  const [country, setCountry] = useState<CountryInterface>()
  const [currentTab, setCurrentTabName] = useState<string | undefined>()

  useEffect(() => {

    if (id) {
      get(`dashboard/countries/view/${id}`).then((res: any) => {
        if (res) {
          setCountry(res.data)
        }
      })
    }
  }, [id])


  const tabs = [
    {
      name: "Competitions",
      link: "competitions",
      content: <Competitions country={country} />,
    },
    {
      name: "Details",
      link: "details",
      content: <Details country={country} />,
    },
  ];

  return (
    <div className="mb-3">
      {
        !loading && country ?
          <div>
            <CountryHeader country={country} currentTab={currentTab} />
            <AutoTabs tabs={tabs} setCurrentTabName={setCurrentTabName} />
          </div>
          :
          <Loader />
      }

    </div>
  )
};

export default Index;
