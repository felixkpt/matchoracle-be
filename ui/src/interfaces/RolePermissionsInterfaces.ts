import { UserInterface } from "./AuthInterfaces";

export interface RoleInterface {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
  updated_at: string;
  user_id: number;
  status: number;
}

export interface PermissionInterface {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
  updated_at: string;
  uri: string;
  title: string;
  icon: string | null;
  hidden: number;
  user_id: number;
  status: number;
  pivot: {
    role_id: number;
    permission_id: number;
  };
}

export interface RouteInterface {
  uri: string;
  methods: string;
  uri_and_methods: string;
  slug: string;
  title: string;
  hidden: boolean;
  icon: string | null;
  checked: boolean;
  filename: string;
  is_public: boolean
}

export interface RouteCollectionInterface {
  folder: string;
  routes: RouteInterface[];
  slug: string;
  title: string;
  hidden: boolean;
  icon: string | null;
  children: RouteCollectionInterface[];
}

export interface RoleRoutePermissionsAndMenuContextInterface {
  // useGetUserRolesAndPermissions
  roleAndPermissions: {
    // guest mode / auth mode
    guestMode: boolean
    isGuestModeSupported: boolean
    user: UserInterface | null | undefined
    loadingUser: boolean
    loadedUser: boolean
    loadingUserError: string | undefined
    setUser: (user: UserInterface) => void
    roles: RoleInterface[]
    routePermissions: PermissionInterface[]
    directPermissions: PermissionInterface[]
    currentRole: RoleInterface | undefined
    setCurrentRole: React.Dispatch<React.SetStateAction<RoleInterface | undefined>>
    loading: boolean
    loaded: boolean
    errors: string | undefined
    reload: () => void
  }

  // useGetRoleRoutePermissions
  roleRoutePermissions: {
    permissions: PermissionInterface[]
    loading: boolean
    loaded: boolean
    errors: string | undefined
    reload: () => void
    key: number
  }

  // useGetRoleMenu
  roleMenu: {
    menu: any
    expandedRootFolders: string[]
    loading: boolean
    loaded: boolean
    errors: string | undefined
    reload: () => void
  }
}