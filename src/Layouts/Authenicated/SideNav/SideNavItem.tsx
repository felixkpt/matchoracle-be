import { Key, useState } from "react";
import { Icon } from '@iconify/react';
import { NavLink } from "react-router-dom";

interface SidebarChildItemInterface {
  item: any;
  title: string;
  icon: string;
  path: string;
  children: any;
}

interface SidebarItemInterface {
  item: {
    title: string;
    icon: any;
    children: [SidebarChildItemInterface];
  };
  path: string;
}

export default function SideNavItem({
  item,
  path,
}: SidebarItemInterface | SidebarChildItemInterface) {
  const [open, setOpen] = useState(false);

  const handleItemClick = (event: React.MouseEvent) => {
    setOpen(!open);
    event.stopPropagation(); // Stop event propagation
  };

  if (item.children.length > 0) {
    return (
      <div
        className={open ? "sidebar-item open" : "sidebar-item"}
        onClick={handleItemClick}
      >
        <div className="sidebar-title cursor-default">
          <span>
            {item.icon && <span className="mr-1"><Icon icon={item.icon} /></span>}
            {item.title}
          </span>
          <Icon icon="bi-chevron-down" />
        </div>
        <div className="sidebar-content">
          {item.children.map(
            (
              child: any | { title: string; icon: any; children: [SidebarChildItemInterface] },
              index: Key | null | undefined
            ) => (
              <SideNavItem key={index} item={child} path={item.path} />
            )
          )}
        </div>
      </div>
    );
  } else {
    return (
      <NavLink
        to={(path + "/" + item.path).replace(/\/+/g, "/") || "#"}
        className="sidebar-item plain cursor-default text-light text-opacity-75"
      >
        {item.icon && <span className="mr-1"><Icon icon={item.icon} /></span>}
        {item.title}
      </NavLink>
    );
  }
}
