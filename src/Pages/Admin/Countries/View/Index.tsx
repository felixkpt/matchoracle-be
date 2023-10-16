import { useEffect, useState } from "react";
import Details from "./Tabs/Details";
import Competitions from "./Tabs/Competitions";
import AutoTabs from "@/components/AutoTabs";
import { useParams } from "react-router-dom";
import { CompetitionInterface, CountryInterface } from "@/interfaces/CompetitionInterface";
import useAxios from "@/hooks/useAxios";
import PageHeader from "@/components/PageHeader";

type Tab = {
  name: string;
  link: string;
  content: JSX.Element;
};

const Index: React.FC = () => {
  const { id } = useParams()
  const { get, loading } = useAxios()
  const [country, setCountry] = useState<CountryInterface>()
  const [competitions, setCompetitions] = useState<CompetitionInterface>()

  useEffect(() => {

    if (id) {
      get(`admin/countries/view/${id}`).then((res: any) => {
        if (res) {
          setCountry(res)
        }
      })
      get(`admin/competitions/country/${id}`).then((res: any) => {
        if (res) {
          setCompetitions(res.data)
        }
      })
    }
  }, [id])


  const tabs = [
    {
      name: "Details",
      link: "details",
      content: <Details country={country} />,
    },
    {
      name: "Competitions",
      link: "competitions",
      content: <Competitions country={country} competitions={competitions} />,
    },
  ];

  return (
    <div className="mb-3">
      {
        !loading && country && <PageHeader title={country.name} />
      }

      <AutoTabs tabs={tabs} active="details" />
    </div>
  )
};

export default Index;
