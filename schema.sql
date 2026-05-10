-- Business Operations Suite - PostgreSQL Schema
-- Generated: 2026-05-03
-- Modules: Core, CRM, Operations, Accounting, Banking, Content, Marketing, Integrations, AI Tools, Administration

CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- =========================================================
-- CORE
-- =========================================================

CREATE TABLE users (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    email varchar(255) UNIQUE,
    username varchar(100) UNIQUE,
    full_name varchar(255) NOT NULL,
    avatar_url text,
    password_hash text,
    api_key varchar(255) UNIQUE,
    user_type text NOT NULL DEFAULT 'human' CHECK (user_type IN ('human', 'ai_agent', 'automation')),
    role text NOT NULL DEFAULT 'user' CHECK (role IN ('user', 'admin', 'moderator')),
    ai_model varchar(120),
    description text,
    is_active boolean NOT NULL DEFAULT true,
    last_login_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid,
    updated_by uuid
);

ALTER TABLE users
    ADD CONSTRAINT fk_users_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_users_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL;

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_api_key ON users(api_key);
CREATE INDEX idx_users_user_type ON users(user_type);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_is_active ON users(is_active);
CREATE INDEX idx_users_created_at ON users(created_at);

CREATE TABLE password_resets (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id uuid NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token_hash text NOT NULL,
    expires_at timestamptz NOT NULL,
    used_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_password_resets_user_id ON password_resets(user_id);
CREATE INDEX idx_password_resets_token_hash ON password_resets(token_hash);
CREATE INDEX idx_password_resets_expires_at ON password_resets(expires_at);

CREATE TABLE change_log (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    table_name varchar(120) NOT NULL,
    record_id uuid NOT NULL,
    action text NOT NULL CHECK (action IN ('create', 'update', 'delete', 'automation')),
    user_id uuid REFERENCES users(id) ON DELETE SET NULL,
    changes jsonb,
    metadata jsonb,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_change_log_table_record ON change_log(table_name, record_id);
CREATE INDEX idx_change_log_user_id ON change_log(user_id);
CREATE INDEX idx_change_log_created_at ON change_log(created_at);

CREATE TABLE settings (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    module varchar(80) NOT NULL,
    key varchar(120) NOT NULL,
    value jsonb NOT NULL,
    description text,
    is_sensitive boolean NOT NULL DEFAULT false,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT uq_settings_module_key UNIQUE (module, key)
);

CREATE INDEX idx_settings_module ON settings(module);
CREATE INDEX idx_settings_module_key ON settings(module, key);

CREATE TABLE companies (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    logo_url text,
    settings jsonb,
    invoice_template jsonb,
    address jsonb,
    tax_settings jsonb,
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_companies_name ON companies(name);
CREATE INDEX idx_companies_is_active ON companies(is_active);

-- =========================================================
-- CRM
-- =========================================================

CREATE TABLE organizations (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    relationship_type text NOT NULL DEFAULT 'other' CHECK (relationship_type IN ('customer', 'vendor', 'contractor', 'affiliate', 'lead', 'partner', 'supplier', 'other')),
    organization_type text NOT NULL DEFAULT 'company' CHECK (organization_type IN ('company', 'nonprofit', 'government', 'individual', 'other')),
    industry varchar(120),
    phone varchar(40),
    website text,
    address jsonb,
    tax_id text,
    is_1099_vendor boolean NOT NULL DEFAULT false,
    source varchar(120),
    last_contacted_at timestamptz,
    status text NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'pending')),
    notes jsonb,
    tags jsonb,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_organizations_name ON organizations(name);
CREATE INDEX idx_organizations_relationship_type ON organizations(relationship_type);
CREATE INDEX idx_organizations_status ON organizations(status);
CREATE INDEX idx_organizations_last_contacted_at ON organizations(last_contacted_at);

CREATE TABLE contacts (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id uuid REFERENCES organizations(id) ON DELETE SET NULL,
    title varchar(120),
    type text NOT NULL DEFAULT 'other' CHECK (type IN ('customer', 'vendor', 'contractor', 'owner', 'employee', 'lead', 'business_contact', 'other')),
    name varchar(255) NOT NULL,
    phone varchar(40),
    email varchar(255),
    address jsonb,
    source varchar(120),
    last_contacted_at timestamptz,
    status text NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    notes jsonb,
    tags jsonb,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_contacts_organization_id ON contacts(organization_id);
CREATE INDEX idx_contacts_type ON contacts(type);
CREATE INDEX idx_contacts_email ON contacts(email);
CREATE INDEX idx_contacts_status ON contacts(status);
CREATE INDEX idx_contacts_last_contacted_at ON contacts(last_contacted_at);

CREATE TABLE leads (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    contact_id uuid REFERENCES contacts(id) ON DELETE SET NULL,
    organization_id uuid REFERENCES organizations(id) ON DELETE SET NULL,
    title varchar(120),
    name varchar(255) NOT NULL,
    phone varchar(40),
    email varchar(255),
    source text CHECK (source IS NULL OR source IN ('website_organic', 'facebook', 'instagram', 'craigslist', 'nextdoor', 'referral', 'physical_media', 'in_person')),
    status text NOT NULL DEFAULT 'uncontacted' CHECK (status IN ('uncontacted', 'contacted', 'quoted', 'booked', 'converted', 'lost')),
    assigned_to uuid REFERENCES users(id) ON DELETE SET NULL,
    next_follow_up timestamptz,
    last_contacted_at timestamptz,
    notes jsonb,
    expected_value numeric(12,2),
    closed_at timestamptz,
    lost_reason text,
    converted_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_leads_status ON leads(status);
CREATE INDEX idx_leads_sentiment ON leads(sentiment);
CREATE INDEX idx_leads_assigned_to ON leads(assigned_to);
CREATE INDEX idx_leads_next_follow_up ON leads(next_follow_up);
CREATE INDEX idx_leads_source ON leads(source);
CREATE INDEX idx_leads_created_at ON leads(created_at);

CREATE TABLE opportunities (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    title varchar(255) NOT NULL,
    description text,
    contact_id uuid REFERENCES contacts(id) ON DELETE SET NULL,
    contact_name varchar(255),
    organization_id uuid REFERENCES organizations(id) ON DELETE SET NULL,
    organization_name varchar(255),
    estimated_value numeric(12,2),
    status text NOT NULL DEFAULT 'new' CHECK (status IN ('new', 'researching', 'in_progress', 'on_hold', 'won', 'lost')),
    priority text NOT NULL DEFAULT 'medium' CHECK (priority IN ('low', 'medium', 'high')),
    target_date date,
    assigned_to uuid REFERENCES users(id) ON DELETE SET NULL,
    notes jsonb,
    loss_reason text,
    won_at timestamptz,
    lost_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_opportunities_status ON opportunities(status);
CREATE INDEX idx_opportunities_priority ON opportunities(priority);
CREATE INDEX idx_opportunities_target_date ON opportunities(target_date);
CREATE INDEX idx_opportunities_assigned_to ON opportunities(assigned_to);

CREATE TABLE tickets (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    ticket_number varchar(40) UNIQUE NOT NULL,
    title varchar(255) NOT NULL,
    description text,
    instructions text,
    instructions_format text NOT NULL DEFAULT 'plain_text' CHECK (instructions_format IN ('plain_text', 'markdown')),
    category text NOT NULL DEFAULT 'other' CHECK (category IN ('billing', 'service_quality', 'technical', 'scheduling', 'complaint', 'refund', 'operations', 'other')),
    priority text NOT NULL DEFAULT 'medium' CHECK (priority IN ('low', 'medium', 'high', 'urgent')),
    status text NOT NULL DEFAULT 'open' CHECK (status IN ('open', 'in_progress', 'waiting_on_customer', 'resolved', 'closed')),
    source text NOT NULL DEFAULT 'human' CHECK (source IN ('human', 'ai_agent')),
    parent_ticket_id uuid REFERENCES tickets(id) ON DELETE SET NULL,
    assigned_to uuid REFERENCES users(id) ON DELETE SET NULL,
    contact_id uuid REFERENCES contacts(id) ON DELETE SET NULL,
    contact_name varchar(255),
    organization_id uuid REFERENCES organizations(id) ON DELETE SET NULL,
    organization_name varchar(255),
    related_work_order_id uuid,
    related_invoice_id uuid,
    notes jsonb,
    metadata jsonb,
    first_response_at timestamptz,
    resolved_at timestamptz,
    closed_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_tickets_ticket_number ON tickets(ticket_number);
CREATE INDEX idx_tickets_status ON tickets(status);
CREATE INDEX idx_tickets_priority ON tickets(priority);
CREATE INDEX idx_tickets_assigned_to ON tickets(assigned_to);
CREATE INDEX idx_tickets_category ON tickets(category);
CREATE INDEX idx_tickets_created_at ON tickets(created_at);

CREATE TABLE estimates (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    estimate_number varchar(40) UNIQUE NOT NULL,
    version_number integer NOT NULL DEFAULT 1,
    parent_estimate_id uuid REFERENCES estimates(id) ON DELETE SET NULL,
    lead_id uuid REFERENCES leads(id) ON DELETE SET NULL,
    contact_id uuid REFERENCES contacts(id) ON DELETE SET NULL,
    contact_name varchar(255),
    organization_id uuid REFERENCES organizations(id) ON DELETE SET NULL,
    organization_name varchar(255),
    title varchar(255) NOT NULL,
    description text,
    line_items jsonb,
    subtotal numeric(12,2),
    discount_amount numeric(12,2) NOT NULL DEFAULT 0,
    tax_amount numeric(12,2),
    total_amount numeric(12,2) NOT NULL,
    payment_terms text,
    status text NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired')),
    valid_until timestamptz,
    notes jsonb,
    metadata jsonb,
    sent_at timestamptz,
    accepted_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_estimates_estimate_number ON estimates(estimate_number);
CREATE INDEX idx_estimates_status ON estimates(status);
CREATE INDEX idx_estimates_valid_until ON estimates(valid_until);
CREATE INDEX idx_estimates_contact_id ON estimates(contact_id);
CREATE INDEX idx_estimates_created_at ON estimates(created_at);

-- =========================================================
-- OPERATIONS
-- =========================================================

CREATE TABLE services (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    description text,
    quote_prompt text,
    category text NOT NULL DEFAULT 'general' CHECK (category IN ('plumbing', 'electrical', 'hvac', 'landscaping', 'cleaning', 'general', 'other')),
    estimated_duration_minutes integer,
    suggested_price numeric(12,2),
    web_page_url text,
    web_content_summary text,
    best_headline varchar(255),
    best_hook varchar(255),
    best_cta varchar(255),
    q_and_a jsonb,
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_services_name ON services(name);
CREATE INDEX idx_services_category ON services(category);
CREATE INDEX idx_services_is_active ON services(is_active);

CREATE TABLE products (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    description text,
    category text NOT NULL DEFAULT 'other' CHECK (category IN ('tools', 'materials', 'consumables', 'equipment', 'safety_gear', 'other')),
    unit_price numeric(12,2),
    unit_of_measure varchar(40),
    is_for_sale boolean NOT NULL DEFAULT false,
    is_internal_use boolean NOT NULL DEFAULT true,
    sku varchar(80),
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT uq_products_sku UNIQUE (sku)
);

CREATE INDEX idx_products_name ON products(name);
CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_is_for_sale ON products(is_for_sale);

CREATE TABLE inventory (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    product_id uuid NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    product_name varchar(255),
    quantity_on_hand numeric(12,2) NOT NULL DEFAULT 0,
    quantity_reserved numeric(12,2) NOT NULL DEFAULT 0,
    reorder_level numeric(12,2),
    reorder_quantity numeric(12,2),
    location varchar(120),
    last_purchased_at timestamptz,
    last_used_at timestamptz,
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT uq_inventory_product UNIQUE (product_id)
);

CREATE INDEX idx_inventory_product_id ON inventory(product_id);
CREATE INDEX idx_inventory_quantity_on_hand ON inventory(quantity_on_hand);
CREATE INDEX idx_inventory_location ON inventory(location);

CREATE TABLE work_orders (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    work_order_number varchar(40) UNIQUE NOT NULL,
    invoice_id uuid,
    invoice_number varchar(40),
    contact_id uuid NOT NULL REFERENCES contacts(id) ON DELETE RESTRICT,
    customer_name varchar(255),
    service_id uuid REFERENCES services(id) ON DELETE SET NULL,
    service_name varchar(255),
    assigned_contractor_id uuid REFERENCES contacts(id) ON DELETE SET NULL,
    assigned_contractor varchar(255),
    contractor_status text NOT NULL DEFAULT 'pending' CHECK (contractor_status IN ('pending', 'accepted', 'rejected')),
    status text NOT NULL DEFAULT 'scheduled' CHECK (status IN ('new', 'scheduled', 'in_progress', 'completed', 'cancelled', 'rework', 'archived')),
    scheduled_date date,
    booking_date date,
    booking_time time,
    address jsonb,
    special_instructions text,
    notes jsonb,
    completed_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_work_orders_work_order_number ON work_orders(work_order_number);
CREATE INDEX idx_work_orders_status ON work_orders(status);
CREATE INDEX idx_work_orders_contact_id ON work_orders(contact_id);
CREATE INDEX idx_work_orders_assigned_contractor_id ON work_orders(assigned_contractor_id);
CREATE INDEX idx_work_orders_scheduled_date ON work_orders(scheduled_date);
CREATE INDEX idx_work_orders_booking_date ON work_orders(booking_date);

CREATE TABLE bookings (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    work_order_id uuid NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    booking_date date NOT NULL,
    start_time time,
    end_time time,
    address jsonb,
    duration_minutes integer,
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_bookings_work_order_id ON bookings(work_order_id);
CREATE INDEX idx_bookings_booking_date ON bookings(booking_date);

CREATE TABLE work_order_materials (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    work_order_id uuid NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    product_id uuid REFERENCES products(id) ON DELETE SET NULL,
    product_name varchar(255),
    quantity numeric(12,2) NOT NULL,
    unit_cost numeric(12,2),
    total_cost numeric(12,2),
    source text NOT NULL DEFAULT 'inventory' CHECK (source IN ('inventory', 'purchased', 'other')),
    is_billable boolean NOT NULL DEFAULT true,
    invoice_item_id uuid,
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_work_order_materials_work_order_id ON work_order_materials(work_order_id);
CREATE INDEX idx_work_order_materials_product_id ON work_order_materials(product_id);
CREATE INDEX idx_work_order_materials_source ON work_order_materials(source);

CREATE TABLE work_order_status_history (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    work_order_id uuid NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    old_status varchar(60),
    new_status varchar(60) NOT NULL,
    changed_by uuid REFERENCES users(id) ON DELETE SET NULL,
    notes text,
    changed_at timestamptz DEFAULT now(),
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_work_order_status_history_work_order_id ON work_order_status_history(work_order_id);
CREATE INDEX idx_work_order_status_history_changed_at ON work_order_status_history(changed_at);

CREATE TABLE work_order_photos (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    work_order_id uuid NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    photo_url text NOT NULL,
    description text,
    uploaded_by uuid REFERENCES users(id) ON DELETE SET NULL,
    uploaded_at timestamptz DEFAULT now(),
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_work_order_photos_work_order_id ON work_order_photos(work_order_id);

CREATE TABLE work_order_documents (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    work_order_id uuid NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    document_name varchar(255) NOT NULL,
    document_url text NOT NULL,
    document_type varchar(60),
    description text,
    uploaded_by uuid REFERENCES users(id) ON DELETE SET NULL,
    uploaded_at timestamptz DEFAULT now(),
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_work_order_documents_work_order_id ON work_order_documents(work_order_id);
CREATE INDEX idx_work_order_documents_document_type ON work_order_documents(document_type);

CREATE TABLE work_order_time_logs (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    work_order_id uuid NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    started_at timestamptz NOT NULL,
    ended_at timestamptz,
    duration_minutes integer,
    activity_type varchar(60),
    notes text,
    logged_by uuid REFERENCES users(id) ON DELETE SET NULL,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_work_order_time_logs_work_order_id ON work_order_time_logs(work_order_id);
CREATE INDEX idx_work_order_time_logs_logged_by ON work_order_time_logs(logged_by);

CREATE TABLE contractor_performance (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    work_order_id uuid NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    contractor_id uuid NOT NULL REFERENCES contacts(id) ON DELETE RESTRICT,
    rating integer CHECK (rating BETWEEN 1 AND 5),
    quality integer CHECK (quality BETWEEN 1 AND 5),
    timeliness integer CHECK (timeliness BETWEEN 1 AND 5),
    communication integer CHECK (communication BETWEEN 1 AND 5),
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_contractor_performance_work_order_id ON contractor_performance(work_order_id);
CREATE INDEX idx_contractor_performance_contractor_id ON contractor_performance(contractor_id);

CREATE TABLE safety_incidents (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    work_order_id uuid NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    incident_date date NOT NULL,
    description text NOT NULL,
    severity text NOT NULL CHECK (severity IN ('low', 'medium', 'high', 'critical')),
    reported_by uuid REFERENCES users(id) ON DELETE SET NULL,
    actions_taken text,
    follow_up_required boolean NOT NULL DEFAULT false,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_safety_incidents_work_order_id ON safety_incidents(work_order_id);
CREATE INDEX idx_safety_incidents_severity ON safety_incidents(severity);

CREATE TABLE reviews (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    work_order_id uuid NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    contact_id uuid REFERENCES contacts(id) ON DELETE SET NULL,
    platform varchar(80),
    review_text text,
    sentiment_score numeric(5,2),
    responded_to boolean NOT NULL DEFAULT false,
    ticket_id uuid REFERENCES tickets(id) ON DELETE SET NULL,
    review_date date,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_reviews_work_order_id ON reviews(work_order_id);
CREATE INDEX idx_reviews_sentiment_score ON reviews(sentiment_score);

CREATE TABLE quality_reviews (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    work_order_id uuid NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    before_photos jsonb,
    after_photos jsonb,
    issues_encountered text,
    safety_issues text,
    customer_signoff_notes text,
    work_order_quality_score integer CHECK (work_order_quality_score BETWEEN 1 AND 10),
    review_id uuid REFERENCES reviews(id) ON DELETE SET NULL,
    approval_status text NOT NULL DEFAULT 'approved' CHECK (approval_status IN ('approved', 'unapproved')),
    performance_id uuid REFERENCES contractor_performance(id) ON DELETE SET NULL,
    safety_incident_id uuid REFERENCES safety_incidents(id) ON DELETE SET NULL,
    submitted_by uuid REFERENCES users(id) ON DELETE SET NULL,
    submitted_at timestamptz DEFAULT now(),
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT uq_quality_reviews_work_order UNIQUE (work_order_id)
);

CREATE INDEX idx_quality_reviews_work_order_id ON quality_reviews(work_order_id);
CREATE INDEX idx_quality_reviews_submitted_at ON quality_reviews(submitted_at);

CREATE TABLE material_returns (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    work_order_id uuid NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    product_id uuid NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
    quantity numeric(12,2) NOT NULL,
    return_date date NOT NULL,
    returned_by uuid REFERENCES users(id) ON DELETE SET NULL,
    reason varchar(255),
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_material_returns_work_order_id ON material_returns(work_order_id);
CREATE INDEX idx_material_returns_product_id ON material_returns(product_id);

CREATE TABLE material_purchases (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    work_order_id uuid REFERENCES work_orders(id) ON DELETE SET NULL,
    product_id uuid NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
    vendor_id uuid REFERENCES organizations(id) ON DELETE SET NULL,
    quantity numeric(12,2) NOT NULL,
    unit_cost numeric(12,2) NOT NULL,
    total_cost numeric(12,2) NOT NULL,
    purchase_date date NOT NULL,
    receipt_url text,
    journal_entry_id uuid,
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_material_purchases_work_order_id ON material_purchases(work_order_id);
CREATE INDEX idx_material_purchases_vendor_id ON material_purchases(vendor_id);
CREATE INDEX idx_material_purchases_purchase_date ON material_purchases(purchase_date);

CREATE TABLE contractor_availability (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    contractor_id uuid NOT NULL REFERENCES contacts(id) ON DELETE CASCADE,
    availability_type text NOT NULL CHECK (availability_type IN ('recurring', 'specific_date', 'blocked')),
    day_of_week integer CHECK (day_of_week BETWEEN 0 AND 6),
    specific_date date,
    start_time time,
    end_time time,
    is_available boolean NOT NULL DEFAULT true,
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_contractor_availability_contractor_id ON contractor_availability(contractor_id);
CREATE INDEX idx_contractor_availability_specific_date ON contractor_availability(specific_date);

CREATE TABLE booking_requests (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    request_number varchar(40) UNIQUE NOT NULL,
    contact_id uuid NOT NULL REFERENCES contacts(id) ON DELETE RESTRICT,
    work_order_id uuid REFERENCES work_orders(id) ON DELETE SET NULL,
    service_id uuid REFERENCES services(id) ON DELETE SET NULL,
    preferred_dates jsonb,
    requested_duration_minutes integer,
    notes text,
    status text NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'proposed', 'confirmed', 'rejected', 'expired')),
    proposed_booking_id uuid REFERENCES bookings(id) ON DELETE SET NULL,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_booking_requests_request_number ON booking_requests(request_number);
CREATE INDEX idx_booking_requests_contact_id ON booking_requests(contact_id);
CREATE INDEX idx_booking_requests_status ON booking_requests(status);

CREATE TABLE sops (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    title varchar(255) NOT NULL,
    description text,
    content text,
    document_url text,
    version varchar(20) NOT NULL DEFAULT '1.0',
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_sops_title ON sops(title);
CREATE INDEX idx_sops_is_active ON sops(is_active);

CREATE TABLE service_sops (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    service_id uuid NOT NULL REFERENCES services(id) ON DELETE CASCADE,
    sop_id uuid NOT NULL REFERENCES sops(id) ON DELETE CASCADE,
    is_required boolean NOT NULL DEFAULT true,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT uq_service_sop UNIQUE (service_id, sop_id)
);

CREATE INDEX idx_service_sops_service_id ON service_sops(service_id);
CREATE INDEX idx_service_sops_sop_id ON service_sops(sop_id);

CREATE TABLE work_order_assignments (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    work_order_id uuid NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    contractor_id uuid NOT NULL REFERENCES contacts(id) ON DELETE RESTRICT,
    assigned_at timestamptz DEFAULT now(),
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_work_order_assignments_work_order_id ON work_order_assignments(work_order_id);
CREATE INDEX idx_work_order_assignments_contractor_id ON work_order_assignments(contractor_id);

CREATE TABLE customer_signoffs (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    work_order_id uuid NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    signed_by_name varchar(255) NOT NULL,
    signed_by_title varchar(120),
    signature_url text,
    signoff_date date NOT NULL,
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT uq_customer_signoff_work_order UNIQUE (work_order_id)
);

CREATE INDEX idx_customer_signoffs_work_order_id ON customer_signoffs(work_order_id);

-- =========================================================
-- ACCOUNTING
-- =========================================================

CREATE TABLE chart_of_accounts (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    code varchar(30) UNIQUE NOT NULL,
    name varchar(255) NOT NULL,
    type text NOT NULL CHECK (type IN ('asset', 'liability', 'equity', 'income', 'expense')),
    description text,
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_chart_of_accounts_type ON chart_of_accounts(type);
CREATE INDEX idx_chart_of_accounts_is_active ON chart_of_accounts(is_active);

CREATE TABLE journal_entries (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    entry_number varchar(40) UNIQUE NOT NULL,
    description text,
    entry_date date NOT NULL,
    total_debits numeric(12,2) DEFAULT 0,
    total_credits numeric(12,2) DEFAULT 0,
    source_module varchar(60),
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_journal_entries_entry_date ON journal_entries(entry_date);
CREATE INDEX idx_journal_entries_created_by ON journal_entries(created_by);

CREATE TABLE journal_entry_lines (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    journal_entry_id uuid NOT NULL REFERENCES journal_entries(id) ON DELETE CASCADE,
    account_id uuid NOT NULL REFERENCES chart_of_accounts(id) ON DELETE RESTRICT,
    debit numeric(12,2) NOT NULL DEFAULT 0,
    credit numeric(12,2) NOT NULL DEFAULT 0,
    description text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT ck_journal_entry_lines_debit_credit CHECK ((debit = 0 AND credit > 0) OR (credit = 0 AND debit > 0))
);

CREATE INDEX idx_journal_entry_lines_journal_entry_id ON journal_entry_lines(journal_entry_id);
CREATE INDEX idx_journal_entry_lines_account_id ON journal_entry_lines(account_id);

CREATE TABLE invoices (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    invoice_number varchar(40) UNIQUE NOT NULL,
    journal_entry_id uuid REFERENCES journal_entries(id) ON DELETE SET NULL,
    work_order_id uuid REFERENCES work_orders(id) ON DELETE SET NULL,
    contact_id uuid REFERENCES contacts(id) ON DELETE SET NULL,
    contact_name varchar(255),
    organization_id uuid REFERENCES organizations(id) ON DELETE SET NULL,
    organization_name varchar(255),
    issue_date date NOT NULL,
    due_date date NOT NULL,
    status text NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'sent', 'paid', 'partial', 'overdue', 'cancelled')),
    subtotal numeric(12,2) NOT NULL,
    tax_amount numeric(12,2) NOT NULL DEFAULT 0,
    total_amount numeric(12,2) NOT NULL,
    amount_paid numeric(12,2) NOT NULL DEFAULT 0,
    amount_due numeric(12,2),
    notes text,
    sent_at timestamptz,
    paid_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_invoices_invoice_number ON invoices(invoice_number);
CREATE INDEX idx_invoices_status ON invoices(status);
CREATE INDEX idx_invoices_contact_id ON invoices(contact_id);
CREATE INDEX idx_invoices_due_date ON invoices(due_date);
CREATE INDEX idx_invoices_work_order_id ON invoices(work_order_id);

CREATE TABLE invoice_items (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    invoice_id uuid NOT NULL REFERENCES invoices(id) ON DELETE CASCADE,
    service_id uuid REFERENCES services(id) ON DELETE SET NULL,
    product_id uuid REFERENCES products(id) ON DELETE SET NULL,
    description text,
    quantity numeric(12,2) NOT NULL,
    unit_price numeric(12,2) NOT NULL,
    total numeric(12,2) NOT NULL,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_invoice_items_invoice_id ON invoice_items(invoice_id);
CREATE INDEX idx_invoice_items_service_id ON invoice_items(service_id);
CREATE INDEX idx_invoice_items_product_id ON invoice_items(product_id);

ALTER TABLE work_orders
    ADD CONSTRAINT fk_work_orders_invoice_id FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL;

ALTER TABLE work_order_materials
    ADD CONSTRAINT fk_work_order_materials_invoice_item_id FOREIGN KEY (invoice_item_id) REFERENCES invoice_items(id) ON DELETE SET NULL;

CREATE TABLE recurring_invoices (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    contact_id uuid REFERENCES contacts(id) ON DELETE SET NULL,
    contact_name varchar(255),
    organization_id uuid REFERENCES organizations(id) ON DELETE SET NULL,
    organization_name varchar(255),
    frequency text NOT NULL CHECK (frequency IN ('weekly', 'monthly', 'quarterly', 'yearly')),
    start_date date NOT NULL,
    end_date date,
    next_invoice_date date,
    subtotal numeric(12,2) NOT NULL,
    tax_amount numeric(12,2) NOT NULL DEFAULT 0,
    total_amount numeric(12,2) NOT NULL,
    status text NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'paused', 'cancelled')),
    last_generated_invoice_id uuid REFERENCES invoices(id) ON DELETE SET NULL,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_recurring_invoices_status ON recurring_invoices(status);
CREATE INDEX idx_recurring_invoices_next_invoice_date ON recurring_invoices(next_invoice_date);

CREATE TABLE bills (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    bill_number varchar(40) UNIQUE NOT NULL,
    vendor_id uuid NOT NULL REFERENCES organizations(id) ON DELETE RESTRICT,
    vendor_name varchar(255),
    issue_date date NOT NULL,
    due_date date NOT NULL,
    status text NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'received', 'approved', 'paid', 'overdue', 'cancelled')),
    subtotal numeric(12,2) NOT NULL,
    tax_amount numeric(12,2) NOT NULL DEFAULT 0,
    total_amount numeric(12,2) NOT NULL,
    amount_paid numeric(12,2) NOT NULL DEFAULT 0,
    notes text,
    journal_entry_id uuid REFERENCES journal_entries(id) ON DELETE SET NULL,
    paid_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_bills_bill_number ON bills(bill_number);
CREATE INDEX idx_bills_vendor_id ON bills(vendor_id);
CREATE INDEX idx_bills_status ON bills(status);
CREATE INDEX idx_bills_due_date ON bills(due_date);

CREATE TABLE payments (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    payment_number varchar(40) UNIQUE NOT NULL,
    journal_entry_id uuid REFERENCES journal_entries(id) ON DELETE SET NULL,
    invoice_id uuid REFERENCES invoices(id) ON DELETE SET NULL,
    bill_id uuid REFERENCES bills(id) ON DELETE SET NULL,
    contact_id uuid REFERENCES contacts(id) ON DELETE SET NULL,
    organization_id uuid REFERENCES organizations(id) ON DELETE SET NULL,
    amount numeric(12,2) NOT NULL,
    payment_date date NOT NULL,
    method text NOT NULL CHECK (method IN ('cash', 'check', 'credit_card', 'bank_transfer', 'other')),
    reference varchar(120),
    payment_direction text NOT NULL DEFAULT 'incoming' CHECK (payment_direction IN ('incoming', 'outgoing')),
    reconciliation_status text NOT NULL DEFAULT 'pending' CHECK (reconciliation_status IN ('pending', 'reconciled', 'failed')),
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_payments_payment_number ON payments(payment_number);
CREATE INDEX idx_payments_invoice_id ON payments(invoice_id);
CREATE INDEX idx_payments_bill_id ON payments(bill_id);
CREATE INDEX idx_payments_payment_date ON payments(payment_date);
CREATE INDEX idx_payments_reconciliation_status ON payments(reconciliation_status);

CREATE TABLE expenses (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    expense_number varchar(40) UNIQUE NOT NULL,
    vendor_id uuid REFERENCES organizations(id) ON DELETE SET NULL,
    amount numeric(12,2) NOT NULL,
    expense_date date NOT NULL,
    category varchar(120),
    description text,
    receipt_url text,
    journal_entry_id uuid REFERENCES journal_entries(id) ON DELETE SET NULL,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_expenses_vendor_id ON expenses(vendor_id);
CREATE INDEX idx_expenses_expense_date ON expenses(expense_date);
CREATE INDEX idx_expenses_category ON expenses(category);

CREATE TABLE payroll (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    payroll_number varchar(40) UNIQUE NOT NULL,
    employee_id uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    pay_period_start date NOT NULL,
    pay_period_end date NOT NULL,
    pay_date date NOT NULL,
    gross_pay numeric(12,2) NOT NULL,
    deductions numeric(12,2) NOT NULL DEFAULT 0,
    net_pay numeric(12,2) NOT NULL,
    status text NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'approved', 'paid')),
    notes text,
    journal_entry_id uuid REFERENCES journal_entries(id) ON DELETE SET NULL,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_payroll_employee_id ON payroll(employee_id);
CREATE INDEX idx_payroll_pay_date ON payroll(pay_date);
CREATE INDEX idx_payroll_status ON payroll(status);

CREATE TABLE tax_forms (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    vendor_organization_id uuid REFERENCES organizations(id) ON DELETE SET NULL,
    vendor_contact_id uuid REFERENCES contacts(id) ON DELETE SET NULL,
    tax_year integer NOT NULL,
    form_type text NOT NULL DEFAULT '1099-NEC' CHECK (form_type IN ('1099-NEC', '1099-MISC', '1099-K', 'W-9')),
    total_paid numeric(12,2) NOT NULL,
    tax_id text,
    address jsonb,
    status text NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'filed', 'sent', 'corrected')),
    filed_date date,
    sent_date date,
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_tax_forms_tax_year ON tax_forms(tax_year);
CREATE INDEX idx_tax_forms_status ON tax_forms(status);

CREATE TABLE tax_settings (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    company_name varchar(255) NOT NULL,
    tax_id text NOT NULL,
    address jsonb,
    contact_name varchar(255),
    contact_phone varchar(40),
    contact_email varchar(255),
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE tax_filings (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tax_year integer NOT NULL,
    form_type varchar(80) NOT NULL,
    filing_date date,
    status text NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'filed', 'accepted', 'rejected')),
    amount_due numeric(12,2),
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_tax_filings_tax_year ON tax_filings(tax_year);
CREATE INDEX idx_tax_filings_status ON tax_filings(status);

CREATE TABLE tax_payments (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tax_filing_id uuid REFERENCES tax_filings(id) ON DELETE SET NULL,
    payment_date date NOT NULL,
    amount numeric(12,2) NOT NULL,
    method varchar(80),
    reference varchar(120),
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_tax_payments_tax_filing_id ON tax_payments(tax_filing_id);
CREATE INDEX idx_tax_payments_payment_date ON tax_payments(payment_date);

CREATE TABLE credits (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    credit_number varchar(40) UNIQUE NOT NULL,
    credit_source text NOT NULL DEFAULT 'customer' CHECK (credit_source IN ('customer', 'vendor')),
    invoice_id uuid REFERENCES invoices(id) ON DELETE SET NULL,
    work_order_id uuid REFERENCES work_orders(id) ON DELETE SET NULL,
    product_id uuid REFERENCES products(id) ON DELETE SET NULL,
    payment_id uuid REFERENCES payments(id) ON DELETE SET NULL,
    contact_id uuid REFERENCES contacts(id) ON DELETE SET NULL,
    vendor_id uuid REFERENCES organizations(id) ON DELETE SET NULL,
    amount numeric(12,2) NOT NULL,
    quantity_returned numeric(12,2),
    credit_date date NOT NULL,
    credit_type text NOT NULL DEFAULT 'refund' CHECK (credit_type IN ('refund', 'store_credit')),
    reason varchar(255),
    status text NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'applied', 'cancelled')),
    journal_entry_id uuid REFERENCES journal_entries(id) ON DELETE SET NULL,
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_credits_invoice_id ON credits(invoice_id);
CREATE INDEX idx_credits_work_order_id ON credits(work_order_id);
CREATE INDEX idx_credits_status ON credits(status);
CREATE INDEX idx_credits_credit_date ON credits(credit_date);

ALTER TABLE material_purchases
    ADD CONSTRAINT fk_material_purchases_journal_entry_id FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id) ON DELETE SET NULL;

ALTER TABLE tickets
    ADD CONSTRAINT fk_tickets_related_invoice_id FOREIGN KEY (related_invoice_id) REFERENCES invoices(id) ON DELETE SET NULL;

-- =========================================================
-- BANKING
-- =========================================================

CREATE TABLE bank_accounts (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    account_type text NOT NULL CHECK (account_type IN ('checking', 'savings', 'cash', 'credit_card', 'other')),
    bank_name varchar(120),
    account_number varchar(120),
    currency varchar(10) NOT NULL DEFAULT 'USD',
    current_balance numeric(14,2) NOT NULL DEFAULT 0,
    is_active boolean NOT NULL DEFAULT true,
    last_reconciled_date date,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_bank_accounts_name ON bank_accounts(name);
CREATE INDEX idx_bank_accounts_account_type ON bank_accounts(account_type);
CREATE INDEX idx_bank_accounts_is_active ON bank_accounts(is_active);

CREATE TABLE bank_cards (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    card_name varchar(255) NOT NULL,
    last4 varchar(4),
    mercury_card_id varchar(120),
    vendor_id uuid REFERENCES organizations(id) ON DELETE SET NULL,
    bank_account_id uuid REFERENCES bank_accounts(id) ON DELETE SET NULL,
    daily_limit numeric(12,2),
    per_transaction_limit numeric(12,2),
    status text NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'paused', 'cancelled')),
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT uq_bank_cards_mercury_card_id UNIQUE (mercury_card_id)
);

CREATE INDEX idx_bank_cards_vendor_id ON bank_cards(vendor_id);
CREATE INDEX idx_bank_cards_status ON bank_cards(status);

CREATE TABLE bank_transactions (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    bank_account_id uuid NOT NULL REFERENCES bank_accounts(id) ON DELETE CASCADE,
    card_id uuid REFERENCES bank_cards(id) ON DELETE SET NULL,
    transaction_date date NOT NULL,
    amount numeric(12,2) NOT NULL,
    transaction_type text NOT NULL CHECK (transaction_type IN ('deposit', 'withdrawal', 'transfer_in', 'transfer_out', 'fee', 'interest', 'other')),
    description text,
    reference varchar(120),
    external_category varchar(120),
    internal_category varchar(120),
    category_source text NOT NULL DEFAULT 'mercury' CHECK (category_source IN ('mercury', 'manual', 'rule')),
    status text NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'categorized', 'reconciled')),
    journal_entry_id uuid REFERENCES journal_entries(id) ON DELETE SET NULL,
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_bank_transactions_bank_account_id ON bank_transactions(bank_account_id);
CREATE INDEX idx_bank_transactions_bank_account_date ON bank_transactions(bank_account_id, transaction_date);
CREATE INDEX idx_bank_transactions_transaction_type ON bank_transactions(transaction_type);
CREATE INDEX idx_bank_transactions_internal_category ON bank_transactions(internal_category);
CREATE INDEX idx_bank_transactions_status ON bank_transactions(status);

CREATE TABLE bank_reconciliations (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    bank_account_id uuid NOT NULL REFERENCES bank_accounts(id) ON DELETE CASCADE,
    statement_date date NOT NULL,
    statement_balance numeric(14,2) NOT NULL,
    book_balance numeric(14,2) NOT NULL,
    difference numeric(14,2) NOT NULL,
    status text NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'reconciled', 'discrepancies')),
    notes text,
    completed_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_bank_reconciliations_bank_account_date ON bank_reconciliations(bank_account_id, statement_date);
CREATE INDEX idx_bank_reconciliations_status ON bank_reconciliations(status);

CREATE TABLE bank_transfers (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    transfer_number varchar(40) UNIQUE NOT NULL,
    from_account_id uuid NOT NULL REFERENCES bank_accounts(id) ON DELETE RESTRICT,
    to_account_id uuid NOT NULL REFERENCES bank_accounts(id) ON DELETE RESTRICT,
    amount numeric(12,2) NOT NULL,
    transfer_date date NOT NULL,
    status text NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'completed', 'failed', 'cancelled')),
    description text,
    journal_entry_id uuid REFERENCES journal_entries(id) ON DELETE SET NULL,
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT ck_bank_transfers_accounts CHECK (from_account_id <> to_account_id)
);

CREATE INDEX idx_bank_transfers_transfer_date ON bank_transfers(transfer_date);
CREATE INDEX idx_bank_transfers_status ON bank_transfers(status);

CREATE TABLE bank_imports (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    bank_account_id uuid NOT NULL REFERENCES bank_accounts(id) ON DELETE CASCADE,
    import_date date NOT NULL,
    file_name varchar(255) NOT NULL,
    file_type text NOT NULL CHECK (file_type IN ('csv', 'ofx', 'qbo', 'qfx', 'pdf', 'other')),
    status text NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'processed', 'failed', 'duplicate')),
    total_transactions integer,
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_bank_imports_bank_account_id ON bank_imports(bank_account_id);
CREATE INDEX idx_bank_imports_status ON bank_imports(status);
CREATE INDEX idx_bank_imports_import_date ON bank_imports(import_date);

CREATE TABLE bank_rules (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    rule_name varchar(255) NOT NULL,
    priority integer NOT NULL DEFAULT 100,
    conditions jsonb NOT NULL,
    action jsonb NOT NULL,
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_bank_rules_rule_name ON bank_rules(rule_name);
CREATE INDEX idx_bank_rules_priority ON bank_rules(priority);
CREATE INDEX idx_bank_rules_is_active ON bank_rules(is_active);

-- =========================================================
-- CONTENT
-- =========================================================

CREATE TABLE blog_categories (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(120) NOT NULL,
    slug varchar(140) UNIQUE NOT NULL,
    description text,
    color varchar(20),
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_blog_categories_is_active ON blog_categories(is_active);

CREATE TABLE blog_tags (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(100) NOT NULL,
    slug varchar(120) UNIQUE NOT NULL,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE blog_posts (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    title varchar(255) NOT NULL,
    slug varchar(180) UNIQUE NOT NULL,
    excerpt text,
    content text NOT NULL,
    featured_image_url text,
    author_id uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    category_id uuid REFERENCES blog_categories(id) ON DELETE SET NULL,
    category varchar(120),
    status text NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'scheduled', 'archived')),
    published_at timestamptz,
    seo_title varchar(255),
    seo_description text,
    seo_keywords jsonb,
    reading_time_minutes integer,
    view_count integer NOT NULL DEFAULT 0,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_blog_posts_status ON blog_posts(status);
CREATE INDEX idx_blog_posts_published_at ON blog_posts(published_at);
CREATE INDEX idx_blog_posts_author_id ON blog_posts(author_id);
CREATE INDEX idx_blog_posts_category_id ON blog_posts(category_id);

CREATE TABLE blog_post_tags (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    blog_post_id uuid NOT NULL REFERENCES blog_posts(id) ON DELETE CASCADE,
    blog_tag_id uuid NOT NULL REFERENCES blog_tags(id) ON DELETE CASCADE,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT uq_blog_post_tag UNIQUE (blog_post_id, blog_tag_id)
);

CREATE INDEX idx_blog_post_tags_blog_post_id ON blog_post_tags(blog_post_id);
CREATE INDEX idx_blog_post_tags_blog_tag_id ON blog_post_tags(blog_tag_id);

CREATE TABLE pages (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    page_slug varchar(180) UNIQUE NOT NULL,
    page_title varchar(255) NOT NULL,
    status text NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'archived')),
    meta_title varchar(255),
    meta_description text,
    sections jsonb NOT NULL,
    is_published boolean NOT NULL DEFAULT true,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_pages_page_slug ON pages(page_slug);
CREATE INDEX idx_pages_status ON pages(status);

CREATE TABLE page_section_types (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(120) NOT NULL,
    slug varchar(120) UNIQUE NOT NULL,
    fields jsonb NOT NULL,
    description text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE image_files (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    filename varchar(255) NOT NULL,
    original_filename varchar(255),
    file_url text NOT NULL,
    thumbnail_url text,
    medium_url text,
    width integer,
    height integer,
    mime_type varchar(100) NOT NULL,
    size_bytes integer,
    tags jsonb,
    library_type text NOT NULL CHECK (library_type IN ('content', 'user_uploads', 'work_order')),
    work_order_id uuid REFERENCES work_orders(id) ON DELETE SET NULL,
    uploaded_by uuid REFERENCES users(id) ON DELETE SET NULL,
    alt_text varchar(255),
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_image_files_library_type ON image_files(library_type);
CREATE INDEX idx_image_files_work_order_id ON image_files(work_order_id);
CREATE INDEX idx_image_files_uploaded_by ON image_files(uploaded_by);
CREATE INDEX idx_image_files_tags ON image_files USING gin(tags);

CREATE TABLE social_media_accounts (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    platform varchar(80) NOT NULL,
    account_name varchar(255) NOT NULL,
    username varchar(120),
    account_type text NOT NULL CHECK (account_type IN ('personal', 'business', 'creator')),
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_social_media_accounts_platform ON social_media_accounts(platform);
CREATE INDEX idx_social_media_accounts_is_active ON social_media_accounts(is_active);

CREATE TABLE social_media_content (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    image_id uuid REFERENCES image_files(id) ON DELETE SET NULL,
    platform varchar(80) NOT NULL,
    crop_url text,
    caption text,
    hashtags jsonb,
    scheduled_at timestamptz,
    status text NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'scheduled', 'published', 'failed')),
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_social_media_content_platform ON social_media_content(platform);
CREATE INDEX idx_social_media_content_status ON social_media_content(status);
CREATE INDEX idx_social_media_content_scheduled_at ON social_media_content(scheduled_at);

CREATE TABLE assets (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    filename varchar(255) NOT NULL,
    original_filename varchar(255),
    file_url text NOT NULL,
    mime_type varchar(120) NOT NULL,
    size_bytes integer,
    width integer,
    height integer,
    tags jsonb,
    alt_text varchar(255),
    uploaded_by uuid REFERENCES users(id) ON DELETE SET NULL,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_assets_mime_type ON assets(mime_type);
CREATE INDEX idx_assets_uploaded_by ON assets(uploaded_by);
CREATE INDEX idx_assets_tags ON assets USING gin(tags);

CREATE TABLE physical_designs (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    design_type text NOT NULL CHECK (design_type IN ('t_shirt', 'business_card', 'flyer', 'sticker', 'door_hanger', 'other')),
    description text,
    files jsonb,
    dimensions varchar(120),
    status text NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'approved', 'archived')),
    latest_version_id uuid,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_physical_designs_design_type ON physical_designs(design_type);
CREATE INDEX idx_physical_designs_status ON physical_designs(status);

CREATE TABLE physical_design_versions (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    physical_design_id uuid NOT NULL REFERENCES physical_designs(id) ON DELETE CASCADE,
    version_number varchar(20) NOT NULL,
    files jsonb,
    notes text,
    status text NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'approved', 'archived')),
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT uq_physical_design_version UNIQUE (physical_design_id, version_number)
);

CREATE INDEX idx_physical_design_versions_design_id ON physical_design_versions(physical_design_id);
CREATE INDEX idx_physical_design_versions_status ON physical_design_versions(status);

ALTER TABLE physical_designs
    ADD CONSTRAINT fk_physical_designs_latest_version FOREIGN KEY (latest_version_id) REFERENCES physical_design_versions(id) ON DELETE SET NULL;

CREATE TABLE product_designs (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    product_id uuid NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    physical_design_id uuid NOT NULL REFERENCES physical_designs(id) ON DELETE CASCADE,
    is_default boolean NOT NULL DEFAULT false,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT uq_product_design UNIQUE (product_id, physical_design_id)
);

CREATE INDEX idx_product_designs_product_id ON product_designs(product_id);
CREATE INDEX idx_product_designs_physical_design_id ON product_designs(physical_design_id);

-- =========================================================
-- MARKETING & ADS
-- =========================================================

CREATE TABLE ad_campaigns (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    description text,
    platform varchar(80),
    status text NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'paused', 'completed')),
    total_budget numeric(12,2),
    amount_spent numeric(12,2) DEFAULT 0,
    roas numeric(8,2),
    start_date date,
    end_date date,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_ad_campaigns_status ON ad_campaigns(status);
CREATE INDEX idx_ad_campaigns_start_date ON ad_campaigns(start_date);

CREATE TABLE campaign_budgets (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    campaign_id uuid NOT NULL REFERENCES ad_campaigns(id) ON DELETE CASCADE,
    total_budget numeric(12,2) NOT NULL,
    daily_budget numeric(12,2),
    amount_spent numeric(12,2) NOT NULL DEFAULT 0,
    remaining_budget numeric(12,2),
    budget_status text NOT NULL DEFAULT 'on_track' CHECK (budget_status IN ('on_track', 'overspending', 'paused', 'completed')),
    start_date date NOT NULL,
    end_date date NOT NULL,
    notes text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT uq_campaign_budget_campaign UNIQUE (campaign_id)
);

CREATE INDEX idx_campaign_budgets_budget_status ON campaign_budgets(budget_status);

CREATE TABLE funnels (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    description text,
    steps jsonb,
    status text NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'archived')),
    overall_conversion_rate numeric(8,2),
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_funnels_status ON funnels(status);

CREATE TABLE landing_pages (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    slug varchar(180) UNIQUE NOT NULL,
    title varchar(255) NOT NULL,
    meta_description text,
    content jsonb NOT NULL,
    ad_id uuid,
    ad_count integer DEFAULT 0,
    conversion_rate numeric(8,2),
    status text NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'archived')),
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_landing_pages_status ON landing_pages(status);

CREATE TABLE ads (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    platform varchar(80) NOT NULL,
    campaign_id uuid REFERENCES ad_campaigns(id) ON DELETE SET NULL,
    funnel_id uuid REFERENCES funnels(id) ON DELETE SET NULL,
    headline varchar(255),
    hook varchar(255),
    description text,
    image_id uuid REFERENCES image_files(id) ON DELETE SET NULL,
    cta_text varchar(120),
    cta_url text,
    landing_page_id uuid REFERENCES landing_pages(id) ON DELETE SET NULL,
    status text NOT NULL DEFAULT 'draft' CHECK (status IN ('draft', 'active', 'paused', 'completed')),
    budget numeric(12,2),
    performance_score numeric(8,2),
    roas numeric(8,2),
    start_date date,
    end_date date,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_ads_campaign_id ON ads(campaign_id);
CREATE INDEX idx_ads_status ON ads(status);
CREATE INDEX idx_ads_platform ON ads(platform);
CREATE INDEX idx_ads_landing_page_id ON ads(landing_page_id);

ALTER TABLE landing_pages
    ADD CONSTRAINT fk_landing_pages_ad_id FOREIGN KEY (ad_id) REFERENCES ads(id) ON DELETE SET NULL;

CREATE TABLE ad_variants (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    ad_id uuid NOT NULL REFERENCES ads(id) ON DELETE CASCADE,
    variant_name varchar(20) NOT NULL,
    headline varchar(255),
    hook varchar(255),
    image_id uuid REFERENCES image_files(id) ON DELETE SET NULL,
    cta_text varchar(120),
    performance_score numeric(8,2),
    is_winner boolean NOT NULL DEFAULT false,
    status text NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'paused', 'archived')),
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_ad_variants_ad_id ON ad_variants(ad_id);
CREATE INDEX idx_ad_variants_is_winner ON ad_variants(is_winner);

CREATE TABLE campaign_performance (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    campaign_id uuid NOT NULL REFERENCES ad_campaigns(id) ON DELETE CASCADE,
    date date NOT NULL,
    impressions integer NOT NULL DEFAULT 0,
    clicks integer NOT NULL DEFAULT 0,
    conversions integer NOT NULL DEFAULT 0,
    cost numeric(12,2) NOT NULL DEFAULT 0,
    revenue numeric(12,2) NOT NULL DEFAULT 0,
    ctr numeric(8,4),
    cpc numeric(12,4),
    roas numeric(8,2),
    metrics jsonb,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT uq_campaign_performance_daily UNIQUE (campaign_id, date)
);

CREATE INDEX idx_campaign_performance_campaign_id ON campaign_performance(campaign_id);
CREATE INDEX idx_campaign_performance_date ON campaign_performance(date);

CREATE TABLE ad_creative_assets (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    ad_id uuid NOT NULL REFERENCES ads(id) ON DELETE CASCADE,
    asset_name varchar(255) NOT NULL,
    asset_type text NOT NULL CHECK (asset_type IN ('image', 'video', 'carousel', 'text')),
    image_id uuid REFERENCES image_files(id) ON DELETE SET NULL,
    video_url text,
    headline varchar(255),
    description text,
    cta_text varchar(120),
    performance_score numeric(8,2),
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_ad_creative_assets_ad_id ON ad_creative_assets(ad_id);
CREATE INDEX idx_ad_creative_assets_asset_type ON ad_creative_assets(asset_type);

CREATE TABLE ad_audiences (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    platform varchar(80) NOT NULL,
    audience_type text NOT NULL CHECK (audience_type IN ('custom', 'lookalike', 'interest', 'remarketing', 'location')),
    description text,
    size_estimate integer,
    status text NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'archived')),
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_ad_audiences_platform ON ad_audiences(platform);
CREATE INDEX idx_ad_audiences_audience_type ON ad_audiences(audience_type);

CREATE TABLE ad_placements (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    ad_id uuid NOT NULL REFERENCES ads(id) ON DELETE CASCADE,
    platform varchar(80) NOT NULL,
    placement varchar(120) NOT NULL,
    impressions integer NOT NULL DEFAULT 0,
    clicks integer NOT NULL DEFAULT 0,
    cost numeric(12,2) NOT NULL DEFAULT 0,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_ad_placements_ad_id ON ad_placements(ad_id);
CREATE INDEX idx_ad_placements_platform ON ad_placements(platform);

CREATE TABLE marketing_attribution (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    contact_id uuid REFERENCES contacts(id) ON DELETE SET NULL,
    ad_id uuid REFERENCES ads(id) ON DELETE SET NULL,
    campaign_id uuid REFERENCES ad_campaigns(id) ON DELETE SET NULL,
    funnel_id uuid REFERENCES funnels(id) ON DELETE SET NULL,
    touchpoint varchar(120) NOT NULL,
    touchpoint_date timestamptz NOT NULL,
    conversion_value numeric(12,2),
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_marketing_attribution_contact_id ON marketing_attribution(contact_id);
CREATE INDEX idx_marketing_attribution_campaign_id ON marketing_attribution(campaign_id);
CREATE INDEX idx_marketing_attribution_touchpoint_date ON marketing_attribution(touchpoint_date);

-- =========================================================
-- INTEGRATIONS
-- =========================================================

CREATE TABLE integrations (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    type text NOT NULL CHECK (type IN ('api', 'webhook', 'snippets')),
    description text,
    status text NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'error')),
    last_connected_at timestamptz,
    configuration jsonb,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_integrations_type ON integrations(type);
CREATE INDEX idx_integrations_status ON integrations(status);

CREATE TABLE api_credentials (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    integration_id uuid NOT NULL REFERENCES integrations(id) ON DELETE CASCADE,
    environment text NOT NULL DEFAULT 'production' CHECK (environment IN ('production', 'sandbox', 'staging', 'development')),
    credential_name varchar(120) NOT NULL,
    credential_value text NOT NULL,
    is_active boolean NOT NULL DEFAULT true,
    expires_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_api_credentials_integration_id ON api_credentials(integration_id);
CREATE INDEX idx_api_credentials_environment ON api_credentials(environment);
CREATE INDEX idx_api_credentials_is_active ON api_credentials(is_active);

CREATE TABLE webhooks (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    integration_id uuid NOT NULL REFERENCES integrations(id) ON DELETE CASCADE,
    direction text NOT NULL CHECK (direction IN ('incoming', 'outgoing')),
    event_type varchar(120) NOT NULL,
    endpoint_url text,
    secret text,
    status text NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'failed', 'disabled')),
    is_active boolean NOT NULL DEFAULT true,
    last_triggered_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_webhooks_integration_id ON webhooks(integration_id);
CREATE INDEX idx_webhooks_direction ON webhooks(direction);
CREATE INDEX idx_webhooks_event_type ON webhooks(event_type);
CREATE INDEX idx_webhooks_status ON webhooks(status);

CREATE TABLE integration_logs (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    integration_id uuid NOT NULL REFERENCES integrations(id) ON DELETE CASCADE,
    webhook_id uuid REFERENCES webhooks(id) ON DELETE SET NULL,
    log_type text NOT NULL CHECK (log_type IN ('api_call', 'webhook', 'error', 'sync', 'other')),
    status text NOT NULL CHECK (status IN ('success', 'failed', 'warning')),
    endpoint text,
    request_payload jsonb,
    response_payload jsonb,
    error_message text,
    duration_ms integer,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_integration_logs_integration_id ON integration_logs(integration_id);
CREATE INDEX idx_integration_logs_log_type ON integration_logs(log_type);
CREATE INDEX idx_integration_logs_status ON integration_logs(status);
CREATE INDEX idx_integration_logs_created_at ON integration_logs(created_at);

CREATE TABLE snippets (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    description text,
    code text NOT NULL,
    snippet_type text NOT NULL CHECK (snippet_type IN ('javascript', 'html', 'css', 'php', 'other')),
    placement text NOT NULL CHECK (placement IN ('head', 'body_start', 'body_end', 'footer', 'specific_page')),
    page_slug varchar(180),
    status text NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    is_active boolean NOT NULL DEFAULT true,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_snippets_name ON snippets(name);
CREATE INDEX idx_snippets_snippet_type ON snippets(snippet_type);
CREATE INDEX idx_snippets_placement ON snippets(placement);
CREATE INDEX idx_snippets_status ON snippets(status);

-- =========================================================
-- AI TOOLS
-- =========================================================

CREATE TABLE tasks (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    title varchar(255) NOT NULL,
    description text,
    instructions text,
    instructions_format text NOT NULL DEFAULT 'plain_text' CHECK (instructions_format IN ('plain_text', 'markdown')),
    category text NOT NULL DEFAULT 'other' CHECK (category IN ('sales', 'accounting', 'operations', 'admin', 'ai_task', 'other')),
    type varchar(120),
    assigned_to uuid NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    assigned_to_name varchar(255),
    priority text NOT NULL DEFAULT 'medium' CHECK (priority IN ('low', 'medium', 'high', 'urgent')),
    status text NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'in_progress', 'completed', 'cancelled', 'failed')),
    due_date timestamptz,
    related_type varchar(80),
    related_id uuid,
    related_lead_id uuid REFERENCES leads(id) ON DELETE SET NULL,
    related_opportunity_id uuid REFERENCES opportunities(id) ON DELETE SET NULL,
    related_contact_id uuid REFERENCES contacts(id) ON DELETE SET NULL,
    related_organization_id uuid REFERENCES organizations(id) ON DELETE SET NULL,
    related_ticket_id uuid REFERENCES tickets(id) ON DELETE SET NULL,
    related_work_order_id uuid REFERENCES work_orders(id) ON DELETE SET NULL,
    notes jsonb,
    metadata jsonb,
    requires_human_approval boolean NOT NULL DEFAULT false,
    approved_by uuid REFERENCES users(id) ON DELETE SET NULL,
    approved_at timestamptz,
    completed_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_tasks_assigned_to ON tasks(assigned_to);
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_priority ON tasks(priority);
CREATE INDEX idx_tasks_due_date ON tasks(due_date);
CREATE INDEX idx_tasks_category ON tasks(category);
CREATE INDEX idx_tasks_related_type_id ON tasks(related_type, related_id);

CREATE TABLE activities (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    title varchar(255) NOT NULL,
    description text,
    type varchar(80) NOT NULL,
    status text NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'scheduled', 'completed', 'cancelled')),
    priority text NOT NULL DEFAULT 'medium' CHECK (priority IN ('low', 'medium', 'high', 'urgent')),
    activity_date timestamptz,
    due_at timestamptz,
    assigned_to uuid REFERENCES users(id) ON DELETE SET NULL,
    lead_id uuid REFERENCES leads(id) ON DELETE SET NULL,
    contact_id uuid REFERENCES contacts(id) ON DELETE SET NULL,
    organization_id uuid REFERENCES organizations(id) ON DELETE SET NULL,
    related_type varchar(120),
    related_id uuid,
    notes jsonb,
    metadata jsonb,
    completed_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_activities_type ON activities(type);
CREATE INDEX idx_activities_status ON activities(status);
CREATE INDEX idx_activities_priority ON activities(priority);
CREATE INDEX idx_activities_activity_date ON activities(activity_date);
CREATE INDEX idx_activities_assigned_to ON activities(assigned_to);
CREATE INDEX idx_activities_lead_id ON activities(lead_id);
CREATE INDEX idx_activities_contact_id ON activities(contact_id);
CREATE INDEX idx_activities_organization_id ON activities(organization_id);
CREATE INDEX idx_activities_related_type_id ON activities(related_type, related_id);

CREATE TABLE ai_agent_profiles (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id uuid NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    prompt_template text,
    allowed_modules jsonb,
    max_concurrency integer NOT NULL DEFAULT 3,
    status text NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'paused', 'offline')),
    last_active_at timestamptz,
    tasks_completed_today integer NOT NULL DEFAULT 0,
    avg_task_duration_seconds integer,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT uq_ai_agent_profiles_user UNIQUE (user_id)
);

CREATE INDEX idx_ai_agent_profiles_status ON ai_agent_profiles(status);
CREATE INDEX idx_ai_agent_profiles_last_active_at ON ai_agent_profiles(last_active_at);

CREATE TABLE ai_task_runs (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    task_id uuid NOT NULL REFERENCES tasks(id) ON DELETE CASCADE,
    agent_user_id uuid REFERENCES users(id) ON DELETE SET NULL,
    status text NOT NULL CHECK (status IN ('queued', 'running', 'completed', 'failed', 'cancelled')),
    started_at timestamptz,
    ended_at timestamptz,
    duration_seconds integer,
    execution_log jsonb,
    error_message text,
    output_summary text,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_ai_task_runs_task_id ON ai_task_runs(task_id);
CREATE INDEX idx_ai_task_runs_agent_user_id ON ai_task_runs(agent_user_id);
CREATE INDEX idx_ai_task_runs_status ON ai_task_runs(status);

CREATE TABLE ai_alerts (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    task_id uuid REFERENCES tasks(id) ON DELETE SET NULL,
    agent_user_id uuid REFERENCES users(id) ON DELETE SET NULL,
    alert_type text NOT NULL CHECK (alert_type IN ('stuck_task', 'failed_task', 'approval_required', 'agent_offline')),
    severity text NOT NULL DEFAULT 'medium' CHECK (severity IN ('low', 'medium', 'high', 'critical')),
    message text NOT NULL,
    is_resolved boolean NOT NULL DEFAULT false,
    resolved_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_ai_alerts_alert_type ON ai_alerts(alert_type);
CREATE INDEX idx_ai_alerts_is_resolved ON ai_alerts(is_resolved);
CREATE INDEX idx_ai_alerts_created_at ON ai_alerts(created_at);

-- =========================================================
-- ADMINISTRATION
-- =========================================================
-- Administration relies heavily on shared tables:
-- users, settings, services, products, inventory, tickets, tasks, change_log
-- Additional admin-centric operational events are tracked below.

CREATE TABLE system_health_events (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    event_type text NOT NULL CHECK (event_type IN ('integration_error', 'job_failed', 'storage_threshold', 'low_stock', 'security')),
    severity text NOT NULL DEFAULT 'warning' CHECK (severity IN ('info', 'warning', 'error', 'critical')),
    title varchar(255) NOT NULL,
    details jsonb,
    related_table_name varchar(120),
    related_record_id uuid,
    resolved_by uuid REFERENCES users(id) ON DELETE SET NULL,
    resolved_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_system_health_events_event_type ON system_health_events(event_type);
CREATE INDEX idx_system_health_events_severity ON system_health_events(severity);
CREATE INDEX idx_system_health_events_created_at ON system_health_events(created_at);

CREATE TABLE low_stock_alerts (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    inventory_id uuid NOT NULL REFERENCES inventory(id) ON DELETE CASCADE,
    product_id uuid NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    product_name varchar(255),
    quantity_on_hand numeric(12,2) NOT NULL,
    reorder_level numeric(12,2),
    status text NOT NULL DEFAULT 'open' CHECK (status IN ('open', 'acknowledged', 'resolved')),
    related_task_id uuid REFERENCES tasks(id) ON DELETE SET NULL,
    resolved_at timestamptz,
    created_at timestamptz DEFAULT now(),
    updated_at timestamptz DEFAULT now(),
    created_by uuid REFERENCES users(id) ON DELETE SET NULL,
    updated_by uuid REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX idx_low_stock_alerts_status ON low_stock_alerts(status);
CREATE INDEX idx_low_stock_alerts_inventory_id ON low_stock_alerts(inventory_id);

-- =========================================================
-- Cross-Module Foreign Keys Added After Table Creation
-- =========================================================

ALTER TABLE tickets
    ADD CONSTRAINT fk_tickets_related_work_order_id FOREIGN KEY (related_work_order_id) REFERENCES work_orders(id) ON DELETE SET NULL;

-- =========================================================
-- Application Layer Rules (non-DB)
-- =========================================================
-- 1) Automations: booking creation -> work_order status scheduled; first ticket reply -> in_progress;
--    full/partial payment -> invoice status paid/partial; overdue date checks -> overdue statuses.
-- 2) Background jobs: on work_order completed create quality review task; on integration failures create tickets;
--    on low inventory create reorder task/alert.
-- 3) AI governance: enforce human approvals for high-impact tasks and block execution until approved.
-- 4) Audit logging: all UI automations and modal-driven related-record creation should write descriptive change_log entries.
