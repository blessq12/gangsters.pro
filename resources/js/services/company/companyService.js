import {
    fetchCompanyDocumentsRequest,
    fetchCompanyLegalsRequest,
    fetchCompanyMainRequest,
} from "../../api/companyApi";
import {
    normalizeCompanyDocument,
    normalizeCompanyLegal,
    normalizeCompanyProfile,
} from "../../domain/company/companyMappers";

export async function fetchCompanyProfile() {
    const payload = await fetchCompanyMainRequest();
    const raw = payload && typeof payload === "object" ? payload.data : null;
    return normalizeCompanyProfile(raw);
}

export async function fetchCompanyLegal() {
    const payload = await fetchCompanyLegalsRequest();
    const raw = payload && typeof payload === "object" ? payload.data : null;
    return normalizeCompanyLegal(raw);
}

export async function fetchCompanyDocuments() {
    const payload = await fetchCompanyDocumentsRequest();
    const raw = Array.isArray(payload?.data) ? payload.data : [];
    return raw.map((row) => normalizeCompanyDocument(row)).filter(Boolean);
}
