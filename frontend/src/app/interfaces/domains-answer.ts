export interface DomainsAnswer {
    pages: {
        current: number,
        total: number
    },
    data: {
        id: number,
        name: string,
        type: string,
        records: number
    }[]
}
