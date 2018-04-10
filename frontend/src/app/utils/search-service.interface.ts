export interface SearchService {
    search(query: string): Promise<SearchServiceResult[]>;
}

export interface SearchServiceResult {
    id: number;
    text: string;
}
