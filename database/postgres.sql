--
-- PostgreSQL database dump
--

-- Dumped from database version 17.0 (DBngin.app)
-- Dumped by pg_dump version 17.0 (DBngin.app)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: generations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.generations (
    id bigint NOT NULL,
    user_id bigint,
    reference uuid NOT NULL,
    pixelforge_id character varying(255),
    prompt text NOT NULL,
    size character varying(20) NOT NULL,
    quality character varying(20) NOT NULL,
    reference_images json NOT NULL,
    status character varying(20) NOT NULL,
    estimated_typical_price bigint NOT NULL,
    estimated_maximum_price bigint NOT NULL,
    rates json,
    input_text_tokens integer,
    input_image_tokens integer,
    output_tokens integer,
    reasoning_tokens integer,
    provider_cost bigint,
    actual_price bigint,
    image_url text,
    image_url_expires_at timestamp(0) without time zone,
    error_code character varying(255),
    error_message character varying(255),
    submission_attempts smallint DEFAULT '0'::smallint NOT NULL,
    provider_created_at timestamp(0) without time zone,
    started_at timestamp(0) without time zone,
    completed_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: generations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.generations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: generations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.generations_id_seq OWNED BY public.generations.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


--
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


--
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    balance bigint DEFAULT '0'::bigint NOT NULL
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: wallet_transactions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.wallet_transactions (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    generation_id bigint,
    type character varying(20) NOT NULL,
    amount bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: wallet_transactions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.wallet_transactions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: wallet_transactions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.wallet_transactions_id_seq OWNED BY public.wallet_transactions.id;


--
-- Name: webhook_events; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.webhook_events (
    id bigint NOT NULL,
    event_id character varying(255) NOT NULL,
    type character varying(255) NOT NULL,
    generation_id bigint,
    payload json NOT NULL,
    processed_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: webhook_events_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.webhook_events_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: webhook_events_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.webhook_events_id_seq OWNED BY public.webhook_events.id;


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: generations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.generations ALTER COLUMN id SET DEFAULT nextval('public.generations_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: wallet_transactions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wallet_transactions ALTER COLUMN id SET DEFAULT nextval('public.wallet_transactions_id_seq'::regclass);


--
-- Name: webhook_events id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.webhook_events ALTER COLUMN id SET DEFAULT nextval('public.webhook_events_id_seq'::regclass);


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cache (key, value, expiration) FROM stdin;
laravel_cache_spatie.permission.cache	a:3:{s:5:"alias";a:0:{}s:11:"permissions";a:0:{}s:5:"roles";a:0:{}}	1795010801
laravel_cache_pixelforge.pricing	a:5:{s:6:"object";s:7:"pricing";s:8:"currency";s:3:"usd";s:4:"unit";s:13:"per_1m_tokens";s:5:"rates";a:4:{s:17:"input_text_tokens";s:4:"5.00";s:18:"input_image_tokens";s:5:"10.00";s:13:"output_tokens";s:5:"40.00";s:16:"reasoning_tokens";s:5:"12.50";}s:6:"tokens";a:5:{s:25:"characters_per_text_token";i:4;s:19:"text_token_variance";d:0.15;s:19:"per_reference_image";i:765;s:6:"output";a:3:{s:3:"low";a:3:{s:9:"1024x1024";i:280;s:9:"1536x1024";i:420;s:9:"1024x1536";i:420;}s:6:"medium";a:3:{s:9:"1024x1024";i:1100;s:9:"1536x1024";i:1650;s:9:"1024x1536";i:1650;}s:4:"high";a:3:{s:9:"1024x1024";i:4300;s:9:"1536x1024";i:6450;s:9:"1024x1536";i:6450;}}s:9:"reasoning";a:3:{s:3:"low";a:2:{s:3:"min";i:0;s:3:"max";i:500;}s:6:"medium";a:2:{s:3:"min";i:200;s:3:"max";i:2000;}s:4:"high";a:2:{s:3:"min";i:1000;s:3:"max";i:8000;}}}}	1794924819
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: generations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.generations (id, user_id, reference, pixelforge_id, prompt, size, quality, reference_images, status, estimated_typical_price, estimated_maximum_price, rates, input_text_tokens, input_image_tokens, output_tokens, reasoning_tokens, provider_cost, actual_price, image_url, image_url_expires_at, error_code, error_message, submission_attempts, provider_created_at, started_at, completed_at, created_at, updated_at) FROM stdin;
1	2	a905ef38-d81a-4c0b-97c1-7fb13b937a4d	gen_01M7MXDKE360EZ5JC37DPJPSXM	test	1024x1024	medium	[]	succeeded	72193750	86262500	{"input_text_tokens":"5.00","input_image_tokens":"10.00","output_tokens":"40.00","reasoning_tokens":"12.50"}	1	0	1100	1163	58542500	73178125	https://pixelforge.promptedit.dev/images/gen_01M7MXDKE360EZ5JC37DPJPSXM.png?expires=1795010940&signature=6c87939a90be308dc56da3215f37120ecd3f847919c94f10e0ab94ea815924f0	2026-11-18 14:09:00	\N	\N	1	2026-11-17 14:08:51	2026-11-17 14:08:52	2026-11-17 14:09:00	2026-11-17 14:08:50	2026-11-17 14:09:03
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.migrations (id, migration, batch) FROM stdin;
\.


--
-- Data for Name: model_has_permissions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.model_has_permissions (permission_id, model_type, model_id) FROM stdin;
\.


--
-- Data for Name: model_has_roles; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.model_has_roles (role_id, model_type, model_id) FROM stdin;
1	App\\Models\\User	1
2	App\\Models\\User	2
2	App\\Models\\User	3
2	App\\Models\\User	4
2	App\\Models\\User	5
2	App\\Models\\User	6
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.permissions (id, name, guard_name, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: role_has_permissions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.role_has_permissions (permission_id, role_id) FROM stdin;
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.roles (id, name, guard_name, created_at, updated_at) FROM stdin;
1	admin	web	2026-11-17 14:06:24	2026-11-17 14:06:24
2	user	web	2026-11-17 14:06:24	2026-11-17 14:06:24
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
rLAUrv3RC0NtTznMMj0woLsZ8LKfEFPtZoU6mtsA	1	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTo2OntzOjY6Il90b2tlbiI7czo0MDoicGJLa0JXOU5mWlF5WUVtV3BocHhiSkQybEQ3TElCQmV2eEFMZ3BiOCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjUyOiJodHRwczovL3BpeGVsZm9yZ2UtYWktaW1hZ2UtZ2VuZXJhdG9yLnRlc3QvZGFzaGJvYXJkIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6ImRlZmUzZGY0NDk1M2UzZTIwZGFhNzUyN2MxZjM5YWE1ZGM4YTM0OWU3NTdjNzM1ODhlMGYyY2EzMjk0OTkxYTYiO30=	1794924455
4uA1WbfdWAyxJFpaHzWe4O3O5RFyckqatB2Edtdc	\N	127.0.0.1	Pixelforge-Webhooks/1.0	YToyOntzOjY6Il90b2tlbiI7czo0MDoicWh5VFY2Slk4TG5NaEhWSUJ5ZzAwdGhxR203R21HcUNMNTdBOEtreCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1794924532
G9gLz0rUOYOqiHUWwThF295u5Z1wfehjS1WUd5Ib	\N	127.0.0.1	Pixelforge-Webhooks/1.0	YToyOntzOjY6Il90b2tlbiI7czo0MDoibXVQbkVZVVVPVTFBQmw1QXFXb1I4SUgxeDY2QzF2UzQ5S2pFUEI2byI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1794924541
uPS8NlfiLcgZz0qbf8KiwFDTVGTZP2K3dPotqy1J	2	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36	YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUVMySE9MYzFQd2pIakNrVGUwNk55dGprM2xmVUdnNkUyYmRzbEJTWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHBzOi8vcGl4ZWxmb3JnZS1haS1pbWFnZS1nZW5lcmF0b3IudGVzdC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6ImUyZjQ2NzNhMGFlZDYxNmU4OTg2YjkxNjY0MzcyYjFhY2VjYzc3YjhiOWM1NWY0ZmZiNThiOWI3NDZlZGNkMjEiO30=	1794924750
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, balance) FROM stdin;
1	Admin	admin@yopmail.com	2026-11-17 14:06:24	$2y$12$ZUBj16G9zjkj1r4H17a3yes2K9q3Du93.YvXQmtdxVcPNDak3wnlu	73ISHHOKV9	2026-11-17 14:06:24	2026-11-17 14:06:24	5000000000
3	Alice	alice@yopmail.com	2026-11-17 14:06:25	$2y$12$e1Mzq8nWIxYaaCrKFYBFXevRwIxoCwup3mG1MTwpUvl0T54y6l4zy	QYDfbhRYWz	2026-11-17 14:06:25	2026-11-17 14:06:25	5000000000
4	Bob	bob@yopmail.com	2026-11-17 14:06:25	$2y$12$0JoapTX2aKhzaFphJCPGwOd4vqBGvMp.EmYGCza59CWLbJxPOIU4G	SBWmtOb9yY	2026-11-17 14:06:25	2026-11-17 14:06:25	5000000000
5	Emma	emma@yopmail.com	2026-11-17 14:06:25	$2y$12$vRt6QQrzfwz6MucOAvaMQOjcbWOuJKULYJ1lf4/mw/g5ZRem2xsmW	A6zzwovdZ9	2026-11-17 14:06:26	2026-11-17 14:06:26	5000000000
6	David	david@yopmail.com	2026-11-17 14:06:26	$2y$12$e.V.wRqu1Qv27PGRAbPmte.NoPIo4cuj5an7Gr4buARMijGjkr0kG	9uLSZZBVfr	2026-11-17 14:06:26	2026-11-17 14:06:26	5000000000
2	John	john@yopmail.com	2026-11-17 14:06:25	$2y$12$rrz7EeqZpaKS/BaH2ab3uOWW4ibsq0g750sNpaGUTY7hdr/UfHtiy	QgdgC0H2TE	2026-11-17 14:06:25	2026-11-17 14:09:03	4926821875
\.


--
-- Data for Name: wallet_transactions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.wallet_transactions (id, user_id, generation_id, type, amount, created_at, updated_at) FROM stdin;
1	1	\N	starting_credit	5000000000	2026-11-17 14:06:24	2026-11-17 14:06:24
2	2	\N	starting_credit	5000000000	2026-11-17 14:06:25	2026-11-17 14:06:25
3	3	\N	starting_credit	5000000000	2026-11-17 14:06:25	2026-11-17 14:06:25
4	4	\N	starting_credit	5000000000	2026-11-17 14:06:25	2026-11-17 14:06:25
5	5	\N	starting_credit	5000000000	2026-11-17 14:06:26	2026-11-17 14:06:26
6	6	\N	starting_credit	5000000000	2026-11-17 14:06:26	2026-11-17 14:06:26
7	2	1	hold	-86262500	2026-11-17 14:08:50	2026-11-17 14:08:50
8	2	1	refund	13084375	2026-11-17 14:09:03	2026-11-17 14:09:03
\.


--
-- Data for Name: webhook_events; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.webhook_events (id, event_id, type, generation_id, payload, processed_at, created_at, updated_at) FROM stdin;
1	evt_01M7MXDMH098SN5JR36Y0HEWBV	generation.processing	1	{"id":"evt_01M7MXDMH098SN5JR36Y0HEWBV","object":"event","type":"generation.processing","created_at":"2026-11-17T14:08:52Z","data":{"object":{"id":"gen_01M7MXDKE360EZ5JC37DPJPSXM","object":"generation","status":"processing","prompt":"test","size":"1024x1024","quality":"medium","reference_images":[],"metadata":{"reference":"a905ef38-d81a-4c0b-97c1-7fb13b937a4d"},"image_url":null,"image_url_expires_at":null,"usage":null,"rates":{"input_text_tokens":"5.00","input_image_tokens":"10.00","output_tokens":"40.00","reasoning_tokens":"12.50"},"error":null,"created_at":"2026-11-17T14:08:51Z","started_at":"2026-11-17T14:08:52Z","completed_at":null}}}	2026-11-17 14:08:54	2026-11-17 14:08:52	2026-11-17 14:08:54
2	evt_01M7MXDWDKSWABBGBE3P01PZF3	generation.succeeded	1	{"id":"evt_01M7MXDWDKSWABBGBE3P01PZF3","object":"event","type":"generation.succeeded","created_at":"2026-11-17T14:09:00Z","data":{"object":{"id":"gen_01M7MXDKE360EZ5JC37DPJPSXM","object":"generation","status":"succeeded","prompt":"test","size":"1024x1024","quality":"medium","reference_images":[],"metadata":{"reference":"a905ef38-d81a-4c0b-97c1-7fb13b937a4d"},"image_url":"https:\\/\\/pixelforge.promptedit.dev\\/images\\/gen_01M7MXDKE360EZ5JC37DPJPSXM.png?expires=1795010940&signature=6c87939a90be308dc56da3215f37120ecd3f847919c94f10e0ab94ea815924f0","image_url_expires_at":"2026-11-18T14:09:00Z","usage":{"input_text_tokens":1,"input_image_tokens":0,"output_tokens":1100,"reasoning_tokens":1163,"total_tokens":2264},"rates":{"input_text_tokens":"5.00","input_image_tokens":"10.00","output_tokens":"40.00","reasoning_tokens":"12.50"},"error":null,"created_at":"2026-11-17T14:08:51Z","started_at":"2026-11-17T14:08:52Z","completed_at":"2026-11-17T14:09:00Z"}}}	2026-11-17 14:09:03	2026-11-17 14:09:01	2026-11-17 14:09:03
\.


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: generations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.generations_id_seq', 1, true);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.jobs_id_seq', 3, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 1, false);


--
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.permissions_id_seq', 1, false);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.roles_id_seq', 2, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 6, true);


--
-- Name: wallet_transactions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.wallet_transactions_id_seq', 8, true);


--
-- Name: webhook_events_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.webhook_events_id_seq', 2, true);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: generations generations_pixelforge_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.generations
    ADD CONSTRAINT generations_pixelforge_id_unique UNIQUE (pixelforge_id);


--
-- Name: generations generations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.generations
    ADD CONSTRAINT generations_pkey PRIMARY KEY (id);


--
-- Name: generations generations_reference_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.generations
    ADD CONSTRAINT generations_reference_unique UNIQUE (reference);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: wallet_transactions wallet_transactions_generation_id_type_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wallet_transactions
    ADD CONSTRAINT wallet_transactions_generation_id_type_unique UNIQUE (generation_id, type);


--
-- Name: wallet_transactions wallet_transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wallet_transactions
    ADD CONSTRAINT wallet_transactions_pkey PRIMARY KEY (id);


--
-- Name: webhook_events webhook_events_event_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.webhook_events
    ADD CONSTRAINT webhook_events_event_id_unique UNIQUE (event_id);


--
-- Name: webhook_events webhook_events_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.webhook_events
    ADD CONSTRAINT webhook_events_pkey PRIMARY KEY (id);


--
-- Name: generations_completed_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX generations_completed_at_index ON public.generations USING btree (completed_at);


--
-- Name: generations_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX generations_status_index ON public.generations USING btree (status);


--
-- Name: generations_user_id_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX generations_user_id_created_at_index ON public.generations USING btree (user_id, created_at);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: wallet_transactions_user_id_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wallet_transactions_user_id_created_at_index ON public.wallet_transactions USING btree (user_id, created_at);


--
-- Name: generations generations_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.generations
    ADD CONSTRAINT generations_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: wallet_transactions wallet_transactions_generation_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wallet_transactions
    ADD CONSTRAINT wallet_transactions_generation_id_foreign FOREIGN KEY (generation_id) REFERENCES public.generations(id) ON DELETE SET NULL;


--
-- Name: wallet_transactions wallet_transactions_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wallet_transactions
    ADD CONSTRAINT wallet_transactions_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: webhook_events webhook_events_generation_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.webhook_events
    ADD CONSTRAINT webhook_events_generation_id_foreign FOREIGN KEY (generation_id) REFERENCES public.generations(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

