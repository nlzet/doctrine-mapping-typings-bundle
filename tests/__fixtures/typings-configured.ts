export type CustomAddress = {
    id: number;
    houseNumber?: string;
};

export type NlzetPerson = {
    id: number;
    name: string;
    extraData: any[];
    createdAt: any;
    updatedAt: any;
    createdDate: number;
    addresses: CustomAddress[];
};
