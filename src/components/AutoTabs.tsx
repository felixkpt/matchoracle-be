import React, { useEffect, useState } from "react";
import { NavLink } from "react-router-dom";

// Define the type for each tab item
type Tab = {
  name: string;
  link: string;
  content: JSX.Element;
};

// Define the props for the AutoTabs component
type Props = {
  tabs: Tab[]; // Array of tab items
  currentTab?: (value: string) => void; // Optional callback to handle current active tab
  active?: string; // Optional prop to specify the currently active tab
};

// AutoTabs component definition
const AutoTabs: React.FC<Props> = ({ tabs, currentTab, active }) => {
  // State to manage the currently open tab
  const [openTab, setOpenTab] = useState<string>(() => {
    // Get the 'tab' URL parameter from the query string
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get("tab");
    const defaultTab = active || tabs[0]?.link; // Use the 'active' prop if provided, or use the first tab
    return tabParam || defaultTab; // Use the 'tab' URL parameter if available, otherwise use the default tab
  });

  // Effect to handle changes in the openTab state and call the currentTab callback if provided
  useEffect(() => {
    if (currentTab) {
      currentTab(openTab);
    }
  }, [openTab, currentTab]);

  // Function to handle tab click event
  function handleTab(e: React.MouseEvent, link: string): void {
    e.preventDefault();
    const newUrl = `${window.location.pathname}?tab=${link}`;
    window.history.pushState({ path: newUrl }, "", newUrl);

    setOpenTab(link);

    // Call the currentTab callback if provided
    if (currentTab) {
      currentTab(link);
    }
  }

  // Get the content of the currently active tab
  const currentTabContent = tabs.find((tab) => tab.link === openTab)?.content;

  // Effect to handle popstate event (back/forward navigation)
  useEffect(() => {
    const handlePopstate = () => {
      const urlParams = new URLSearchParams(window.location.search);
      const tabParam = urlParams.get("tab");
      setOpenTab(tabParam || tabs[0]?.link); // Use the 'tab' URL parameter if available, otherwise use the first tab
    };

    window.addEventListener("popstate", handlePopstate);

    return () => {
      window.removeEventListener("popstate", handlePopstate);
    };
  }, [tabs]);

  // Render the AutoTabs component
  return (
    <div>
      <ul className="nav nav-tabs" role="tablist">

        {tabs.map((tab) => (
          <li key={tab.name} className="nav-item" role="presentation">
            <NavLink
              to={`?tab=${tab.link}`} // Set the URL parameter for the tab link
              onClick={(e) => handleTab(e, tab.link)} 
              className={`nav-link ${openTab === tab.link ? "active bg-body-secondary" : "border-bottom"}`}
              data-toggle="tab"
            >
              {tab.name}
            </NavLink>
          </li>
        ))}
      </ul>
      <div className="tab-content mt-2">
        <div className="transition-opacity duration-500">{currentTabContent}</div>
      </div>
    </div>
  );
};

// Author: Felix Kiptoo (https://github.com/felixkpt)
export default AutoTabs;
