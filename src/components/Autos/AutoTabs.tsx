import Str from "@/utils/Str";
import React, { useEffect, useState } from "react";
import { NavLink } from "react-router-dom";
import PageHeader from "../PageHeader";
import { PageHeaderInterface, TabInterface } from "@/interfaces/UncategorizedInterfaces";
import useAxios from "@/hooks/useAxios";
import { Icon } from "@iconify/react/dist/iconify.js";

// Define the props for the AutoTabs component
interface Props extends Omit<PageHeaderInterface, 'title'> {
  tabs: TabInterface[];
  setCurrentTabName?: (value: string) => void;
  active?: string;
  title?: string;
  countsUrl?: string
};

// AutoTabs component definition
const AutoTabs: React.FC<Props> = ({ tabs, setCurrentTabName, active, title, action, actionText, actionLink, permission, method = 'post', actionTargetId, listUrl, countsUrl, setRecord }) => {
  // State to manage the currently open tab
  const [localOpenTab, setLocalOpenTab] = useState<TabInterface | undefined>(() => {
    // Get the 'tab' URL parameter from the query string
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get("tab");
    const defaultTab = tabParam || active || Str.slug(tabs[0].name);

    return tabs.find((tab) => Str.slug(tab.name) === defaultTab)

  });

  const { get } = useAxios()

  // Effect to handle changes in the localOpenTab state and call the currentTab callback if provided
  useEffect(() => {
    if (localOpenTab && setCurrentTabName) {
      setCurrentTabName(localOpenTab.name);
    }
  }, [localOpenTab, setCurrentTabName]);

  // Function to handle tab click event
  function handleTab(e: React.MouseEvent, tabName: string): void {

    const newUrl = `${window.location.pathname}?tab=${tabName}`;
    window.history.pushState({ path: newUrl }, "", newUrl);

    var found = tabs.find((tab) => Str.slug(tab.name) === tabName)

    if (found) {
      setLocalOpenTab(found);
      // Call the currentTab callback if provided
      if (setCurrentTabName) {
        setCurrentTabName(tabName);
      }
    }

  }
  const [key, setKey] = useState<number>(0)

  // Effect to handle popstate event (back/forward navigation)
  useEffect(() => {
    const handlePopstate = () => {

      const urlParams = new URLSearchParams(window.location.search);
      const tabParam = urlParams.get("tab");
      let defaultTab = tabParam || Str.slug(tabs[0].name)

      let found = tabs.find((tab) => Str.slug(tab.name) === defaultTab)
      setLocalOpenTab(found);
    };

    window.addEventListener("popstate", handlePopstate);

    return () => {
      window.removeEventListener("popstate", handlePopstate);
    };

  }, []);

  useEffect(() => {

    if (countsUrl) {
      var tabNames = tabs.map(d => Str.slug(d['name']));

      get(countsUrl + 'tabs', { params: { tabs: tabNames } }).then((res) => {
        if (res)
          for (let key in res) {
            var val = res[key]
            var elm = document.querySelector(`.auto-tabs .${key} .tab-items`)
            if (elm)
              elm.textContent = val
          }

      })
    }
  }, [countsUrl])

  // Render the AutoTabs component
  return (
    <div className="auto-tabs" key={key}>
      {
        title &&
        <PageHeader title={`${title} - ${localOpenTab?.label || localOpenTab?.name}`} action={action} actionText={actionText} actionLink={actionLink} permission={permission} method={method} actionTargetId={actionTargetId} listUrl={listUrl} setRecord={setRecord} />
      }
      <div className="tabs-section">

        <ul className="nav nav-tabs" role="tablist">

          {tabs.map((tab) => (
            <li key={tab.name} className="nav-item" role="presentation">
              <NavLink
                to={`?tab=${Str.slug(tab.name)}`} // Set the URL parameter for the tab link
                onClick={(e) => handleTab(e, Str.slug(tab.name))}
                className={`nav-link ${Str.slug(tab.name)} ${Str.slug(localOpenTab?.name) === Str.slug(tab.name) ? "active-autotab" : "border-bottom"}`}
                data-toggle="tab"
              >
                <small className="tab-items">1</small>
                <span className="tab-name">{tab.label || tab.name}</span>
              </NavLink>
            </li>
          ))}
          <li className="nav-item d-flex align-items-center justify-content-center position-relative" style={{ minWidth: '30px', fontSize: '22px', }}>
            <div className="shadow-sm position-absolute rounded" style={{top: '3px', left: '5px'}}>
              <small title='Click to reload current tab' className='cursor-pointer rounded px-1' onClick={() => setKey(curr => curr + 1)}><Icon icon="mdi:reload" /></small>
            </div>
          </li>
        </ul>
        <div className="tab-content mt-2">
          <div className="transition-opacity duration-500">{localOpenTab?.content}</div>
        </div>
      </div>
    </div>
  );
};

// Author: Felix Kiptoo (https://github.com/felixkpt)
export default AutoTabs;
