PGDMP                         r            lincut    9.3.4    9.3.4 O    �           0    0    ENCODING    ENCODING        SET client_encoding = 'UTF8';
                       false            �           0    0 
   STDSTRINGS 
   STDSTRINGS     (   SET standard_conforming_strings = 'on';
                       false            �           1262    16385    lincut    DATABASE     �   CREATE DATABASE lincut WITH TEMPLATE = template0 ENCODING = 'UTF8' LC_COLLATE = 'Russian_Russia.1251' LC_CTYPE = 'Russian_Russia.1251';
    DROP DATABASE lincut;
             postgres    false                        2615    2200    public    SCHEMA        CREATE SCHEMA public;
    DROP SCHEMA public;
             postgres    false            �           0    0    SCHEMA public    COMMENT     6   COMMENT ON SCHEMA public IS 'standard public schema';
                  postgres    false    5            �           0    0    public    ACL     �   REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;
                  postgres    false    5            �            3079    11750    plpgsql 	   EXTENSION     ?   CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;
    DROP EXTENSION plpgsql;
                  false            �           0    0    EXTENSION plpgsql    COMMENT     @   COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';
                       false    182                       1247    16439    restriction_mode    TYPE     M   CREATE TYPE restriction_mode AS ENUM (
    'all',
    'time',
    'count'
);
 #   DROP TYPE public.restriction_mode;
       public       postgres    false    5            �           0    0    TYPE restriction_mode    COMMENT     q   COMMENT ON TYPE restriction_mode IS 'Режимы ограничения оптимизации раскроя';
            public       postgres    false    530            �            1259    16436    job_job_id_seq    SEQUENCE     p   CREATE SEQUENCE job_job_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 %   DROP SEQUENCE public.job_job_id_seq;
       public       postgres    false    5            �            1259    16445    job    TABLE     �   CREATE TABLE job (
    id integer DEFAULT nextval('job_job_id_seq'::regclass) NOT NULL,
    title character varying(100) NOT NULL,
    map_ready boolean DEFAULT false NOT NULL
);
    DROP TABLE public.job;
       public         postgres    false    170    5            �           0    0 	   TABLE job    COMMENT     9   COMMENT ON TABLE job IS 'Сменное задание';
            public       postgres    false    171            �           0    0    COLUMN job.title    COMMENT     S   COMMENT ON COLUMN job.title IS 'Название сменного задания';
            public       postgres    false    171            �           0    0    COLUMN job.map_ready    COMMENT     U   COMMENT ON COLUMN job.map_ready IS 'Готовность карты раскроя';
            public       postgres    false    171            �            1259    16470    job_material    TABLE     �   CREATE TABLE job_material (
    id integer NOT NULL,
    job_id integer NOT NULL,
    wcad_material_id integer NOT NULL,
    wcad_material_length real NOT NULL
);
     DROP TABLE public.job_material;
       public         postgres    false    5            �           0    0    TABLE job_material    COMMENT     y   COMMENT ON TABLE job_material IS 'Материал для раскроя в данном сменном задании';
            public       postgres    false    174            �           0    0    COLUMN job_material.job_id    COMMENT     �   COMMENT ON COLUMN job_material.job_id IS 'Сменное задание, за которым закреплен материал';
            public       postgres    false    174            �           0    0 $   COLUMN job_material.wcad_material_id    COMMENT     {   COMMENT ON COLUMN job_material.wcad_material_id IS 'Номер (ID) материала из базы данных WinCAD';
            public       postgres    false    174            �           0    0 (   COLUMN job_material.wcad_material_length    COMMENT     n   COMMENT ON COLUMN job_material.wcad_material_length IS 'Длина материала, миллиметры';
            public       postgres    false    174            �            1259    16496    job_material_detail    TABLE     �   CREATE TABLE job_material_detail (
    id integer NOT NULL,
    wcad_detail_id integer NOT NULL,
    wcad_detail_length real,
    job_material_id integer NOT NULL
);
 '   DROP TABLE public.job_material_detail;
       public         postgres    false    5            �           0    0    TABLE job_material_detail    COMMENT     a   COMMENT ON TABLE job_material_detail IS 'Деталь для раскроя материала';
            public       postgres    false    177            �           0    0 )   COLUMN job_material_detail.wcad_detail_id    COMMENT     z   COMMENT ON COLUMN job_material_detail.wcad_detail_id IS 'Номер (ID) детали из базы данных WinCAD';
            public       postgres    false    177            �           0    0 -   COLUMN job_material_detail.wcad_detail_length    COMMENT     m   COMMENT ON COLUMN job_material_detail.wcad_detail_length IS 'Длина детали, миллиметры';
            public       postgres    false    177            �           0    0 *   COLUMN job_material_detail.job_material_id    COMMENT     �   COMMENT ON COLUMN job_material_detail.job_material_id IS 'Номер материала, за которым закреплена деталь';
            public       postgres    false    177            �            1259    16494    job_material_detail_id_seq    SEQUENCE     |   CREATE SEQUENCE job_material_detail_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 1   DROP SEQUENCE public.job_material_detail_id_seq;
       public       postgres    false    5    177            �           0    0    job_material_detail_id_seq    SEQUENCE OWNED BY     K   ALTER SEQUENCE job_material_detail_id_seq OWNED BY job_material_detail.id;
            public       postgres    false    176            �            1259    16473    job_material_id_seq    SEQUENCE     u   CREATE SEQUENCE job_material_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 *   DROP SEQUENCE public.job_material_id_seq;
       public       postgres    false    174    5            �           0    0    job_material_id_seq    SEQUENCE OWNED BY     =   ALTER SEQUENCE job_material_id_seq OWNED BY job_material.id;
            public       postgres    false    175            �            1259    16459 	   job_order    TABLE     p   CREATE TABLE job_order (
    id integer NOT NULL,
    job_id integer NOT NULL,
    order_id integer NOT NULL
);
    DROP TABLE public.job_order;
       public         postgres    false    5            �           0    0    TABLE job_order    COMMENT     �   COMMENT ON TABLE job_order IS 'Заказ, закрепленный за сменным заданием для раскроя';
            public       postgres    false    173            �           0    0    COLUMN job_order.job_id    COMMENT     �   COMMENT ON COLUMN job_order.job_id IS 'Номер сменного задания, к которому прикрепленн данный заказ';
            public       postgres    false    173            �           0    0    COLUMN job_order.order_id    COMMENT     c   COMMENT ON COLUMN job_order.order_id IS 'Номер заказа из базы данных WCAD';
            public       postgres    false    173            �            1259    16457    job_order_id_seq1    SEQUENCE     s   CREATE SEQUENCE job_order_id_seq1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 (   DROP SEQUENCE public.job_order_id_seq1;
       public       postgres    false    5    173            �           0    0    job_order_id_seq1    SEQUENCE OWNED BY     8   ALTER SEQUENCE job_order_id_seq1 OWNED BY job_order.id;
            public       postgres    false    172            �            1259    16515    map_operation    TABLE     ^   CREATE TABLE map_operation (
    id integer NOT NULL,
    job_material_id integer NOT NULL
);
 !   DROP TABLE public.map_operation;
       public         postgres    false    5            �           0    0    TABLE map_operation    COMMENT     �   COMMENT ON TABLE map_operation IS 'Операция (материал) карты раскроя, привязанная к сменному заданию. У сменного задания только одна карта раскроя.';
            public       postgres    false    179            �           0    0 $   COLUMN map_operation.job_material_id    COMMENT     �   COMMENT ON COLUMN map_operation.job_material_id IS 'Номер (ID) материала, который раскроен в данной операции';
            public       postgres    false    179            �            1259    16529    map_operation_detail    TABLE     �   CREATE TABLE map_operation_detail (
    id integer NOT NULL,
    job_material_detail_id integer NOT NULL,
    map_operation_id integer NOT NULL
);
 (   DROP TABLE public.map_operation_detail;
       public         postgres    false    5            �           0    0    TABLE map_operation_detail    COMMENT     J   COMMENT ON TABLE map_operation_detail IS 'Деталь операции';
            public       postgres    false    181            �            1259    16527    map_operation_detail_id_seq    SEQUENCE     }   CREATE SEQUENCE map_operation_detail_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 2   DROP SEQUENCE public.map_operation_detail_id_seq;
       public       postgres    false    5    181            �           0    0    map_operation_detail_id_seq    SEQUENCE OWNED BY     M   ALTER SEQUENCE map_operation_detail_id_seq OWNED BY map_operation_detail.id;
            public       postgres    false    180            �            1259    16513    map_operation_id_seq    SEQUENCE     v   CREATE SEQUENCE map_operation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 +   DROP SEQUENCE public.map_operation_id_seq;
       public       postgres    false    5    179            �           0    0    map_operation_id_seq    SEQUENCE OWNED BY     ?   ALTER SEQUENCE map_operation_id_seq OWNED BY map_operation.id;
            public       postgres    false    178            C           2604    16475    id    DEFAULT     d   ALTER TABLE ONLY job_material ALTER COLUMN id SET DEFAULT nextval('job_material_id_seq'::regclass);
 >   ALTER TABLE public.job_material ALTER COLUMN id DROP DEFAULT;
       public       postgres    false    175    174            D           2604    16499    id    DEFAULT     r   ALTER TABLE ONLY job_material_detail ALTER COLUMN id SET DEFAULT nextval('job_material_detail_id_seq'::regclass);
 E   ALTER TABLE public.job_material_detail ALTER COLUMN id DROP DEFAULT;
       public       postgres    false    176    177    177            B           2604    16462    id    DEFAULT     _   ALTER TABLE ONLY job_order ALTER COLUMN id SET DEFAULT nextval('job_order_id_seq1'::regclass);
 ;   ALTER TABLE public.job_order ALTER COLUMN id DROP DEFAULT;
       public       postgres    false    172    173    173            E           2604    16547    id    DEFAULT     f   ALTER TABLE ONLY map_operation ALTER COLUMN id SET DEFAULT nextval('map_operation_id_seq'::regclass);
 ?   ALTER TABLE public.map_operation ALTER COLUMN id DROP DEFAULT;
       public       postgres    false    178    179    179            F           2604    16532    id    DEFAULT     t   ALTER TABLE ONLY map_operation_detail ALTER COLUMN id SET DEFAULT nextval('map_operation_detail_id_seq'::regclass);
 F   ALTER TABLE public.map_operation_detail ALTER COLUMN id DROP DEFAULT;
       public       postgres    false    181    180    181            �          0    16445    job 
   TABLE DATA               ,   COPY job (id, title, map_ready) FROM stdin;
    public       postgres    false    171   �V       �           0    0    job_job_id_seq    SEQUENCE SET     6   SELECT pg_catalog.setval('job_job_id_seq', 88, true);
            public       postgres    false    170            �          0    16470    job_material 
   TABLE DATA               S   COPY job_material (id, job_id, wcad_material_id, wcad_material_length) FROM stdin;
    public       postgres    false    174   �V       �          0    16496    job_material_detail 
   TABLE DATA               _   COPY job_material_detail (id, wcad_detail_id, wcad_detail_length, job_material_id) FROM stdin;
    public       postgres    false    177   �V       �           0    0    job_material_detail_id_seq    SEQUENCE SET     F   SELECT pg_catalog.setval('job_material_detail_id_seq', 232798, true);
            public       postgres    false    176            �           0    0    job_material_id_seq    SEQUENCE SET     =   SELECT pg_catalog.setval('job_material_id_seq', 8176, true);
            public       postgres    false    175            �          0    16459 	   job_order 
   TABLE DATA               2   COPY job_order (id, job_id, order_id) FROM stdin;
    public       postgres    false    173   �V       �           0    0    job_order_id_seq1    SEQUENCE SET     :   SELECT pg_catalog.setval('job_order_id_seq1', 169, true);
            public       postgres    false    172            �          0    16515    map_operation 
   TABLE DATA               5   COPY map_operation (id, job_material_id) FROM stdin;
    public       postgres    false    179   W       �          0    16529    map_operation_detail 
   TABLE DATA               U   COPY map_operation_detail (id, job_material_detail_id, map_operation_id) FROM stdin;
    public       postgres    false    181   ,W       �           0    0    map_operation_detail_id_seq    SEQUENCE SET     E   SELECT pg_catalog.setval('map_operation_detail_id_seq', 3581, true);
            public       postgres    false    180            �           0    0    map_operation_id_seq    SEQUENCE SET     =   SELECT pg_catalog.setval('map_operation_id_seq', 829, true);
            public       postgres    false    178            Q           2606    16501    job_material_detail_pkey 
   CONSTRAINT     c   ALTER TABLE ONLY job_material_detail
    ADD CONSTRAINT job_material_detail_pkey PRIMARY KEY (id);
 V   ALTER TABLE ONLY public.job_material_detail DROP CONSTRAINT job_material_detail_pkey;
       public         postgres    false    177    177            N           2606    16481    job_material_pkey 
   CONSTRAINT     U   ALTER TABLE ONLY job_material
    ADD CONSTRAINT job_material_pkey PRIMARY KEY (id);
 H   ALTER TABLE ONLY public.job_material DROP CONSTRAINT job_material_pkey;
       public         postgres    false    174    174            K           2606    16464    job_order_pkey 
   CONSTRAINT     O   ALTER TABLE ONLY job_order
    ADD CONSTRAINT job_order_pkey PRIMARY KEY (id);
 B   ALTER TABLE ONLY public.job_order DROP CONSTRAINT job_order_pkey;
       public         postgres    false    173    173            H           2606    16452    job_pkey 
   CONSTRAINT     C   ALTER TABLE ONLY job
    ADD CONSTRAINT job_pkey PRIMARY KEY (id);
 6   ALTER TABLE ONLY public.job DROP CONSTRAINT job_pkey;
       public         postgres    false    171    171            T           2606    16520    map_material_pkey 
   CONSTRAINT     V   ALTER TABLE ONLY map_operation
    ADD CONSTRAINT map_material_pkey PRIMARY KEY (id);
 I   ALTER TABLE ONLY public.map_operation DROP CONSTRAINT map_material_pkey;
       public         postgres    false    179    179            X           2606    16534    map_operation_detail_pkey 
   CONSTRAINT     e   ALTER TABLE ONLY map_operation_detail
    ADD CONSTRAINT map_operation_detail_pkey PRIMARY KEY (id);
 X   ALTER TABLE ONLY public.map_operation_detail DROP CONSTRAINT map_operation_detail_pkey;
       public         postgres    false    181    181            O           1259    16512 ,   fki_job_material_detail_job_material_id_fkey    INDEX     p   CREATE INDEX fki_job_material_detail_job_material_id_fkey ON job_material_detail USING btree (job_material_id);
 @   DROP INDEX public.fki_job_material_detail_job_material_id_fkey;
       public         postgres    false    177            L           1259    16487    fki_job_material_job_id_fkey    INDEX     P   CREATE INDEX fki_job_material_job_id_fkey ON job_material USING btree (job_id);
 0   DROP INDEX public.fki_job_material_job_id_fkey;
       public         postgres    false    174            U           1259    16541 4   fki_map_operation_detail_job_material_detail_id_fkey    INDEX     �   CREATE INDEX fki_map_operation_detail_job_material_detail_id_fkey ON map_operation_detail USING btree (job_material_detail_id);
 H   DROP INDEX public.fki_map_operation_detail_job_material_detail_id_fkey;
       public         postgres    false    181            V           1259    16553 .   fki_map_operation_detail_map_operation_id_fkey    INDEX     t   CREATE INDEX fki_map_operation_detail_map_operation_id_fkey ON map_operation_detail USING btree (map_operation_id);
 B   DROP INDEX public.fki_map_operation_detail_map_operation_id_fkey;
       public         postgres    false    181            R           1259    16526 &   fki_map_operation_job_material_id_fkey    INDEX     d   CREATE INDEX fki_map_operation_job_material_id_fkey ON map_operation USING btree (job_material_id);
 :   DROP INDEX public.fki_map_operation_job_material_id_fkey;
       public         postgres    false    179            I           1259    16493    job_order_id_idx    INDEX     =   CREATE INDEX job_order_id_idx ON job_order USING btree (id);
 $   DROP INDEX public.job_order_id_idx;
       public         postgres    false    173            [           2606    16507 (   job_material_detail_job_material_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY job_material_detail
    ADD CONSTRAINT job_material_detail_job_material_id_fkey FOREIGN KEY (job_material_id) REFERENCES job_material(id) ON UPDATE CASCADE ON DELETE CASCADE;
 f   ALTER TABLE ONLY public.job_material_detail DROP CONSTRAINT job_material_detail_job_material_id_fkey;
       public       postgres    false    174    1870    177            Z           2606    16488    job_material_job_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY job_material
    ADD CONSTRAINT job_material_job_id_fkey FOREIGN KEY (job_id) REFERENCES job(id) ON UPDATE CASCADE ON DELETE CASCADE;
 O   ALTER TABLE ONLY public.job_material DROP CONSTRAINT job_material_job_id_fkey;
       public       postgres    false    174    1864    171            Y           2606    16465    job_order_job_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY job_order
    ADD CONSTRAINT job_order_job_id_fkey FOREIGN KEY (job_id) REFERENCES job(id) ON UPDATE CASCADE ON DELETE CASCADE;
 I   ALTER TABLE ONLY public.job_order DROP CONSTRAINT job_order_job_id_fkey;
       public       postgres    false    173    1864    171            ^           2606    16559 0   map_operation_detail_job_material_detail_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY map_operation_detail
    ADD CONSTRAINT map_operation_detail_job_material_detail_id_fkey FOREIGN KEY (job_material_detail_id) REFERENCES job_material_detail(id) ON UPDATE CASCADE ON DELETE CASCADE;
 o   ALTER TABLE ONLY public.map_operation_detail DROP CONSTRAINT map_operation_detail_job_material_detail_id_fkey;
       public       postgres    false    1873    181    177            ]           2606    16554 *   map_operation_detail_map_operation_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY map_operation_detail
    ADD CONSTRAINT map_operation_detail_map_operation_id_fkey FOREIGN KEY (map_operation_id) REFERENCES map_operation(id) ON UPDATE CASCADE ON DELETE CASCADE;
 i   ALTER TABLE ONLY public.map_operation_detail DROP CONSTRAINT map_operation_detail_map_operation_id_fkey;
       public       postgres    false    1876    179    181            \           2606    16521 "   map_operation_job_material_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY map_operation
    ADD CONSTRAINT map_operation_job_material_id_fkey FOREIGN KEY (job_material_id) REFERENCES job_material(id) ON UPDATE CASCADE ON DELETE CASCADE;
 Z   ALTER TABLE ONLY public.map_operation DROP CONSTRAINT map_operation_job_material_id_fkey;
       public       postgres    false    174    1870    179            �      x������ � �      �      x������ � �      �      x������ � �      �      x������ � �      �      x������ � �      �      x������ � �     